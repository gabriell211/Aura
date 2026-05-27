<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Cache;
use App\Actions\GenerateMonthlyInvoicesAction;
use App\Enums\ContractType;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Equipment;
use App\Models\User;
use App\Jobs\SyncEquipmentJob;
use App\Services\PrintwayyIntegrationService;
use App\Services\TrialLifecycleService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('aura:trial-lifecycle', function (): void {
    $stats = app(TrialLifecycleService::class)->processAllTrials();

    $this->info('Trial lifecycle executed.');
    $this->table(['processed', 'notified', 'links_created', 'errors'], [[$stats['processed'], $stats['notified'], $stats['links_created'], $stats['errors']]]);
})->purpose('Process trial reminders and InfinitePay checkout generation for Aura SaaS trials.');

Artisan::command('aura:sync-printwayy {tenantId=1} {--recreate-contracts}', function (int $tenantId): int {
    $lock = Cache::lock("aura-sync-printwayy-tenant-{$tenantId}", 7200);

    if (! $lock->get()) {
        $this->warn("Sincronizacao da Printwayy ja esta em execucao para tenant {$tenantId}.");

        return 1;
    }

    try {
        $company = Company::query()->find($tenantId);

        if ($company === null) {
            $this->error("Empresa tenant {$tenantId} nao encontrada.");

            return 1;
        }

        app()->instance('tenant_id', $tenantId);

        $before = [
            'equipment' => Equipment::query()->count(),
            'clients' => Client::query()->count(),
            'contracts' => Contract::query()->count(),
        ];

        $startedAt = microtime(true);
        $stats = app(PrintwayyIntegrationService::class)->syncTenant($tenantId);
        $elapsedSeconds = (int) round(microtime(true) - $startedAt);

        $contractsCreated = 0;
        $equipmentsLinked = 0;

        if ((bool) $this->option('recreate-contracts')) {
            $adminId = User::query()->where('tenant_id', $tenantId)->value('id');
            $clients = Client::query()->where('tenant_id', $tenantId)->get();

            foreach ($clients as $client) {
                $hasContract = Contract::query()
                    ->where('tenant_id', $tenantId)
                    ->where('client_id', $client->id)
                    ->exists();

                if ($hasContract) {
                    continue;
                }

                $baseCode = sprintf('CTR-SP-%04d', (int) $client->id);
                $code = $baseCode;
                $suffix = 1;

                while (Contract::query()->where('tenant_id', $tenantId)->where('code', $code)->exists()) {
                    $suffix++;
                    $code = $baseCode.'-'.$suffix;
                }

                $contract = Contract::query()->create([
                    'tenant_id' => $tenantId,
                    'client_id' => (int) $client->id,
                    'client_unit_id' => null,
                    'code' => $code,
                    'type' => ContractType::FRANCHISE->value,
                    'start_date' => now()->toDateString(),
                    'end_date' => null,
                    'monthly_fee' => 0,
                    'included_bw_pages' => 0,
                    'included_color_pages' => 0,
                    'bw_overage_price' => 0.044,
                    'color_overage_price' => 0.670,
                    'status' => 'active',
                    'payment_method' => 'cobranca_bancaria',
                    'reading_period' => 'tarde',
                    'reading_fixed_day' => 27,
                    'reading_start_date' => '2026-05-05',
                    'reading_end_date' => null,
                    'due_days' => 15,
                    'print_type' => 'suporte_setor',
                    'counter_display_mode' => 'pt_color',
                    'allow_extension' => false,
                    'show_observation' => false,
                    'issue_boleto' => true,
                    'unified_boleto' => false,
                    'unified_contract' => false,
                    'external_contract_number' => null,
                    'global_bw_franchise_value' => 0,
                    'global_color_franchise_value' => 0,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]);

                $linkedNow = Equipment::query()
                    ->where('tenant_id', $tenantId)
                    ->where('client_id', $client->id)
                    ->whereNull('contract_id')
                    ->update(['contract_id' => (int) $contract->id]);

                $contractsCreated++;
                $equipmentsLinked += $linkedNow;
            }
        }

        $after = [
            'equipment' => Equipment::query()->count(),
            'clients' => Client::query()->count(),
            'contracts' => Contract::query()->count(),
        ];

        $this->info("Sincronizacao finalizada para tenant {$tenantId} em {$elapsedSeconds}s.");
        $this->table(
            ['ok', 'eq_processados', 'eq_criados', 'eq_atualizados', 'leituras', 'alertas', 'chamados_ext', 'erros'],
            [[
                (int) ($stats['ok'] ?? false),
                (int) ($stats['equipment_processed'] ?? 0),
                (int) ($stats['equipment_created'] ?? 0),
                (int) ($stats['equipment_updated'] ?? 0),
                (int) ($stats['meter_reads_created'] ?? 0),
                (int) ($stats['alerts_processed'] ?? 0),
                (int) ($stats['external_tickets_synced'] ?? 0),
                count((array) ($stats['errors'] ?? [])),
            ]]
        );
        $this->table(
            ['antes_eq', 'depois_eq', 'antes_clientes', 'depois_clientes', 'antes_contratos', 'depois_contratos', 'contratos_criados', 'equip_vinculados'],
            [[
                $before['equipment'],
                $after['equipment'],
                $before['clients'],
                $after['clients'],
                $before['contracts'],
                $after['contracts'],
                $contractsCreated,
                $equipmentsLinked,
            ]]
        );

        return (bool) ($stats['ok'] ?? false) ? 0 : 1;
    } catch (\Throwable $exception) {
        $this->error('Falha na sincronizacao: '.$exception->getMessage());

        return 1;
    } finally {
        optional($lock)->release();
    }
})->purpose('Sincroniza Printwayy por tenant e opcionalmente recria contratos base para clientes sem contrato.');

Schedule::call(function (): void {
    $action = app(GenerateMonthlyInvoicesAction::class);

    Company::query()
        ->where('is_active', true)
        ->pluck('id')
        ->each(fn (int $tenantId) => $action->execute($tenantId));
})->monthlyOn(1, '02:00')->name('erp-monthly-billing');

Schedule::call(function (): void {
    Company::query()
        ->where('is_active', true)
        ->pluck('id')
        ->each(fn (int $tenantId) => SyncEquipmentJob::dispatch($tenantId));
})->everyTenMinutes()->name('erp-sync-equipment');

Schedule::call(function (): void {
    // Placeholder: SLA watchdog and proactive alerts.
})->everyFiveMinutes()->name('erp-sla-watchdog');

Schedule::call(function (): void {
    app(TrialLifecycleService::class)->processAllTrials();
})->dailyAt('09:00')->timezone('America/Sao_Paulo')->name('aura-trial-lifecycle');
