<?php

namespace App\Services;

use App\DTOs\MeterReadData;
use App\Enums\TicketStatus;
use App\Models\Client;
use App\Models\Company;
use App\Models\Equipment;
use App\Models\EquipmentAlert;
use App\Models\MeterRead;
use App\Models\Ticket;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class PrintwayyIntegrationService
{
    public function __construct(
        private readonly MeterReadService $meterReadService,
        private readonly TicketAutomationService $ticketAutomationService,
    ) {
    }

    public function ingestMeterRead(int $tenantId, array $payload, string $source = 'webhook'): MeterRead
    {
        app()->instance('tenant_id', $tenantId);

        $equipment = $this->resolveEquipmentFromPayload($payload);

        if ($equipment === null) {
            throw ValidationException::withMessages([
                'equipment' => 'Equipamento não encontrado para esta leitura.',
            ]);
        }

        $monoTotal = (int) $this->pickFirst($payload, ['mono_total', 'bw_total', 'black_total', 'counter_mono'], 0);
        $colorTotal = (int) $this->pickFirst($payload, ['color_total', 'counter_color'], 0);

        return $this->meterReadService->register(new MeterReadData(
            tenantId: $tenantId,
            equipmentId: (int) $equipment->id,
            readAt: (string) $this->pickFirst($payload, ['read_at', 'captured_at', 'timestamp'], now()->toDateTimeString()),
            monoTotal: max($monoTotal, 0),
            colorTotal: max($colorTotal, 0),
            source: $source,
            rawPayload: $payload,
        ));
    }

    public function ingestAlert(int $tenantId, array $payload): Ticket
    {
        app()->instance('tenant_id', $tenantId);

        $equipment = $this->resolveEquipmentFromPayload($payload);
        $clientId = $this->resolveTicketClientId($tenantId, $payload, $equipment);

        if ($clientId < 1) {
            throw ValidationException::withMessages([
                'client_id' => 'Não foi possível resolver o cliente para o alerta recebido.',
            ]);
        }

        $severity = $this->normalizePriority((string) $this->pickFirst($payload, ['severity', 'priority', 'level'], 'medium'));
        $alertType = (string) $this->pickFirst($payload, ['alert_type', 'type', 'event'], 'generic');
        $message = (string) $this->pickFirst($payload, ['message', 'description'], 'Alerta recebido da Printwayy.');

        if ($equipment !== null) {
            EquipmentAlert::query()->create([
                'tenant_id' => $tenantId,
                'equipment_id' => (int) $equipment->id,
                'alert_type' => $alertType,
                'severity' => $severity,
                'message' => $message,
                'status' => 'open',
                'opened_at' => now(),
            ]);
        }

        $externalReference = $this->resolveExternalReference($payload)
            ?? $this->buildExternalReference('printwayy_alert', $payload, $equipment?->serial_number, $alertType);

        return $this->ticketAutomationService->openForAlert([
            'tenant_id' => $tenantId,
            'client_id' => $clientId,
            'equipment_id' => $equipment?->id,
            'title' => (string) $this->pickFirst($payload, ['title'], 'Printwayy: '.$alertType),
            'description' => $message,
            'priority' => $severity,
            'status' => $this->normalizeTicketStatus((string) $this->pickFirst($payload, ['ticket_status', 'status', 'state'], TicketStatus::OPEN->value)),
            'origin' => 'monitoring',
            'opened_at' => $this->normalizeDateTimeValue($this->pickFirst($payload, ['opened_at', 'created_at', 'timestamp'])),
            'closed_at' => $this->normalizeDateTimeValue($this->pickFirst($payload, ['closed_at', 'resolved_at', 'finished_at'])),
            'external_source' => 'printwayy_alert',
            'external_reference' => $externalReference,
            'external_payload_hash' => $this->payloadHash($payload),
        ]);
    }

    public function ingestTicket(int $tenantId, array $payload): Ticket
    {
        app()->instance('tenant_id', $tenantId);

        $equipment = $this->resolveEquipmentFromPayload($payload);
        $clientId = $this->resolveTicketClientId($tenantId, $payload, $equipment);

        if ($clientId < 1) {
            throw ValidationException::withMessages([
                'client_id' => 'Não foi possível resolver o cliente para o chamado recebido.',
            ]);
        }

        $ticketKind = (string) $this->pickFirst($payload, ['ticket_type', 'category', 'type'], 'ticket');
        $externalReference = $this->resolveExternalReference($payload)
            ?? $this->buildExternalReference('printwayy_ticket', $payload, $equipment?->serial_number, $ticketKind);

        return $this->ticketAutomationService->openForAlert([
            'tenant_id' => $tenantId,
            'client_id' => $clientId,
            'equipment_id' => $equipment?->id,
            'title' => (string) $this->pickFirst(
                $payload,
                ['title', 'subject'],
                sprintf('Printwayy Ticket %s', $externalReference)
            ),
            'description' => (string) $this->pickFirst($payload, ['description', 'message', 'details'], 'Chamado sincronizado da Printwayy.'),
            'priority' => $this->normalizePriority((string) $this->pickFirst($payload, ['priority', 'severity', 'level'], 'medium')),
            'status' => $this->normalizeTicketStatus((string) $this->pickFirst($payload, ['status', 'state'], TicketStatus::OPEN->value)),
            'origin' => 'monitoring',
            'opened_at' => $this->normalizeDateTimeValue($this->pickFirst($payload, ['opened_at', 'created_at', 'timestamp'])),
            'closed_at' => $this->normalizeDateTimeValue($this->pickFirst($payload, ['closed_at', 'resolved_at', 'finished_at'])),
            'external_source' => 'printwayy_ticket',
            'external_reference' => $externalReference,
            'external_payload_hash' => $this->payloadHash($payload),
        ]);
    }

    public function syncTenant(int $tenantId): array
    {
        $company = Company::query()->find($tenantId);

        if ($company === null || ! $company->is_active) {
            return [
                'ok' => false,
                'reason' => 'tenant_invalido_ou_inativo',
            ];
        }

        if (! $this->isPrintwayyEnabled($company)) {
            return [
                'ok' => false,
                'reason' => 'printwayy_desabilitado',
            ];
        }

        if (! $this->hasPrintwayyCredentials($company)) {
            return [
                'ok' => false,
                'reason' => 'printwayy_config_incompleta',
            ];
        }

        app()->instance('tenant_id', $tenantId);

        $stats = [
            'ok' => true,
            'equipment_processed' => 0,
            'equipment_created' => 0,
            'equipment_updated' => 0,
            'meter_reads_created' => 0,
            'alerts_processed' => 0,
            'tickets_opened' => 0,
            'external_tickets_processed' => 0,
            'external_tickets_synced' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        try {
            $equipmentSnapshots = $this->fetchEquipmentPayload($company);
        } catch (Throwable $exception) {
            Log::warning('Printwayy equipment fetch failed.', [
                'tenant_id' => $tenantId,
                'message' => $exception->getMessage(),
            ]);

            return [
                'ok' => false,
                'reason' => 'printwayy_equipment_fetch_failed',
                'message' => $exception->getMessage(),
            ];
        }

        foreach ($equipmentSnapshots as $snapshot) {
            $stats['equipment_processed']++;

            try {
                $result = $this->upsertEquipmentFromSnapshot($tenantId, (array) $snapshot);

                if ($result === null) {
                    $stats['skipped']++;

                    continue;
                }

                $stats[$result['created'] ? 'equipment_created' : 'equipment_updated']++;

                if ($this->hasAnyKey($snapshot, ['mono_total', 'bw_total', 'black_total', 'counter_mono', 'color_total', 'counter_color'])) {
                    $this->ingestMeterRead($tenantId, (array) $snapshot, 'api');
                    $stats['meter_reads_created']++;
                }

                $printerExternalId = (string) ($result['printwayy_printer_id'] ?? '');

                if ($printerExternalId !== '' && $this->syncCountersForEquipment(
                    tenantId: $tenantId,
                    company: $company,
                    equipmentId: (int) $result['equipment_id'],
                    printerExternalId: $printerExternalId,
                    snapshot: (array) $snapshot,
                )) {
                    $stats['meter_reads_created']++;
                }
            } catch (Throwable $exception) {
                $stats['errors'][] = $exception->getMessage();
            }
        }

        try {
            foreach ($this->fetchAlertsPayload($company) as $alert) {
                $stats['alerts_processed']++;

                $this->ingestAlert($tenantId, (array) $alert);
                $stats['tickets_opened']++;
            }
        } catch (Throwable $exception) {
            Log::warning('Printwayy alerts fetch failed.', [
                'tenant_id' => $tenantId,
                'message' => $exception->getMessage(),
            ]);
        }

        try {
            foreach ($this->fetchTicketsPayload($company) as $ticket) {
                $stats['external_tickets_processed']++;

                $this->ingestTicket($tenantId, (array) $ticket);
                $stats['external_tickets_synced']++;
            }
        } catch (Throwable $exception) {
            Log::warning('Printwayy tickets fetch failed.', [
                'tenant_id' => $tenantId,
                'message' => $exception->getMessage(),
            ]);
        }

        $company->forceFill([
            'printwayy_last_sync_at' => now(),
        ])->save();

        return $stats;
    }

    private function isPrintwayyEnabled(Company $company): bool
    {
        return (bool) $company->printwayy_enabled || filled(config('services.printwayy.api_base_url'));
    }

    private function hasPrintwayyCredentials(Company $company): bool
    {
        return filled($company->printwayy_api_base_url ?: config('services.printwayy.api_base_url'))
            && filled($company->printwayy_api_token ?: config('services.printwayy.api_token'));
    }

    private function fetchEquipmentPayload(Company $company): array
    {
        $endpoint = (string) config('services.printwayy.equipment_endpoint', '/api/equipment');

        return $this->fetchOffsetPaginatedPayload($company, $endpoint);
    }

    private function fetchAlertsPayload(Company $company): array
    {
        $endpoint = (string) config('services.printwayy.alerts_endpoint', '/api/alerts');

        return $this->fetchOffsetPaginatedPayload($company, $endpoint);
    }

    private function fetchTicketsPayload(Company $company): array
    {
        $endpoint = (string) config('services.printwayy.tickets_endpoint', '');

        if (blank($endpoint)) {
            return [];
        }

        return $this->fetchOffsetPaginatedPayload($company, $endpoint);
    }

    private function fetchPrinterCountersPayload(Company $company, string $printerExternalId): array
    {
        $template = (string) config('services.printwayy.counters_endpoint', '/printers/{printer_id}/counters');
        $template = trim($template);

        if ($template === '') {
            return [];
        }

        $endpoint = str_replace(
            ['{printer_id}', '{id}', ':printer_id', ':id'],
            [$printerExternalId, $printerExternalId, $printerExternalId, $printerExternalId],
            $template,
        );

        if (! str_starts_with($endpoint, '/')) {
            $endpoint = '/'.$endpoint;
        }

        $response = $this->clientForCompany($company)->get($endpoint, $this->tenantQueryParams($company))->throw()->json();

        return $this->normalizeListPayload($response);
    }

    private function syncCountersForEquipment(int $tenantId, Company $company, int $equipmentId, string $printerExternalId, array $snapshot): bool
    {
        $counters = $this->fetchPrinterCountersPayload($company, $printerExternalId);
        $counterRead = $this->extractCounterReadFromPayload($counters, $snapshot);

        if ($counterRead === null) {
            return false;
        }

        $monoTotal = (int) $counterRead['mono_total'];
        $colorTotal = (int) $counterRead['color_total'];
        $readAt = (string) $counterRead['read_at'];

        $latestRead = MeterRead::query()
            ->where('tenant_id', $tenantId)
            ->where('equipment_id', $equipmentId)
            ->latest('read_at')
            ->first();

        if ($latestRead !== null
            && (int) $latestRead->mono_total === $monoTotal
            && (int) $latestRead->color_total === $colorTotal) {
            return false;
        }

        $this->meterReadService->register(new MeterReadData(
            tenantId: $tenantId,
            equipmentId: $equipmentId,
            readAt: $readAt,
            monoTotal: $monoTotal,
            colorTotal: $colorTotal,
            source: 'api',
            rawPayload: [
                'source' => 'printwayy_counters',
                'printer_id' => $printerExternalId,
                'counters' => $counters,
            ],
        ));

        return true;
    }

    private function clientForCompany(Company $company): PendingRequest
    {
        $baseUrl = (string) ($company->printwayy_api_base_url ?: config('services.printwayy.api_base_url'));
        $token = (string) ($company->printwayy_api_token ?: config('services.printwayy.api_token'));
        $verifySsl = (bool) config('services.printwayy.verify_ssl', true);

        return Http::acceptJson()
            ->baseUrl(rtrim($baseUrl, '/'))
            ->timeout((int) config('services.printwayy.timeout', 15))
            ->retry(
                max((int) config('services.printwayy.retries', 3), 1),
                max((int) config('services.printwayy.retry_sleep_ms', 250), 0),
            )
            ->withOptions(['verify' => $verifySsl])
            ->withHeaders(['printwayy-key' => $token])
            ->withToken($token);
    }

    private function tenantQueryParams(Company $company): array
    {
        $workspaceId = $company->printwayy_workspace_id ?: config('services.printwayy.workspace_id');

        return filled($workspaceId) ? ['workspace_id' => $workspaceId] : [];
    }

    private function upsertEquipmentFromSnapshot(int $tenantId, array $snapshot): ?array
    {
        $serialNumber = (string) $this->pickFirst($snapshot, ['serial_number', 'serial', 'serialNumber'], '');
        $printerExternalId = $this->resolvePrinterExternalId($snapshot);

        if ($serialNumber === '') {
            return null;
        }

        $equipment = Equipment::query()
            ->where('serial_number', $serialNumber)
            ->first();

        $payload = [
            'manufacturer' => (string) $this->pickFirst($snapshot, ['manufacturer', 'brand', 'vendor'], $equipment?->manufacturer ?? 'N/A'),
            'model' => (string) $this->pickFirst($snapshot, ['model', 'model_name'], $equipment?->model ?? 'N/A'),
            'serial_number' => $serialNumber,
            'asset_tag' => $this->pickFirst($snapshot, ['asset_tag', 'assetTag', 'patrimonio', 'patrimony', 'asset_number', 'assetNumber']),
            'ip_address' => $this->pickFirst($snapshot, ['ip_address', 'ip', 'ipAddress']),
            'mac_address' => $this->pickFirst($snapshot, ['mac_address', 'mac', 'macAddress']),
            'location' => $this->resolveLocationLabel($snapshot),
            'status' => $this->normalizeEquipmentStatus((string) $this->pickFirst($snapshot, ['status', 'state'], 'online')),
        ];

        $resolvedClientId = $this->resolveClientId($tenantId, $snapshot);

        if ($equipment !== null) {
            if ($resolvedClientId !== null && (int) $equipment->client_id !== $resolvedClientId) {
                $payload['client_id'] = $resolvedClientId;
            }

            $equipment->fill($payload);
            $equipment->save();

            return [
                'created' => false,
                'equipment_id' => (int) $equipment->id,
                'printwayy_printer_id' => $printerExternalId,
            ];
        }

        $clientId = $resolvedClientId;

        if ($clientId === null) {
            return null;
        }

        $equipment = Equipment::query()->create($payload + [
            'tenant_id' => $tenantId,
            'client_id' => $clientId,
            'client_unit_id' => $this->resolveOptionalTenantId($tenantId, 'client_units', (int) $this->pickFirst($snapshot, ['client_unit_id'], 0)),
            'contract_id' => $this->resolveOptionalTenantId($tenantId, 'contracts', (int) $this->pickFirst($snapshot, ['contract_id'], 0)),
            'installed_at' => $this->pickFirst($snapshot, ['installed_at', 'install_date']),
        ]);

        return [
            'created' => true,
            'equipment_id' => (int) $equipment->id,
            'printwayy_printer_id' => $printerExternalId,
        ];
    }

    private function resolveEquipmentFromPayload(array $payload): ?Equipment
    {
        $equipmentId = (int) $this->pickFirst($payload, ['equipment_id'], 0);

        if ($equipmentId > 0) {
            return Equipment::query()->find($equipmentId);
        }

        $serialNumber = (string) $this->pickFirst($payload, ['serial_number', 'serial', 'serialNumber'], '');

        if ($serialNumber === '') {
            return null;
        }

        return Equipment::query()
            ->where('serial_number', $serialNumber)
            ->first();
    }

    private function resolveClientId(int $tenantId, array $snapshot): ?int
    {
        $rawClientId = (int) $this->pickFirst($snapshot, ['client_id'], 0);

        if ($rawClientId > 0) {
            $existing = Client::query()->where('tenant_id', $tenantId)->where('id', $rawClientId)->first();

            if ($existing !== null) {
                return (int) $existing->id;
            }
        }

        $clientDocument = trim((string) $this->pickFirst($snapshot, [
            'client_document',
            'document',
            'customer.document',
            'customer.cnpj',
            'customer.cpf',
            'location.cnpj',
            'location.cpf',
        ], ''));

        if ($clientDocument !== '') {
            $existing = Client::query()->where('tenant_id', $tenantId)->where('document', $clientDocument)->first();

            if ($existing !== null) {
                return (int) $existing->id;
            }
        }

        $clientName = trim((string) $this->pickFirst($snapshot, [
            'client_name',
            'customer_name',
            'customer.name',
            'location.customerName',
            'location.businessName',
            'location.address.name',
        ], ''));

        if ($clientName !== '') {
            $existing = Client::query()->where('tenant_id', $tenantId)->where('name', $clientName)->first();

            if ($existing !== null) {
                return (int) $existing->id;
            }
        }

        $clientEmail = trim((string) $this->pickFirst($snapshot, ['client_email', 'email', 'customer.email', 'location.email'], ''));
        $clientPhone = trim((string) $this->pickFirst($snapshot, ['client_phone', 'phone', 'telephone', 'customer.phone', 'location.phone'], ''));

        // Fallback to keep equipment import working when Printwayy doesn't send explicit client mapping.
        $fallbackName = $clientName !== '' ? $clientName : 'Cliente Printwayy';
        $fallbackDocument = $clientDocument !== '' ? $clientDocument : null;

        $existingFallback = Client::query()
            ->where('tenant_id', $tenantId)
            ->where('name', $fallbackName)
            ->when(
                $fallbackDocument !== null,
                fn ($query) => $query->where('document', $fallbackDocument),
            )
            ->first();

        if ($existingFallback !== null) {
            return (int) $existingFallback->id;
        }

        $created = Client::query()->create([
            'tenant_id' => $tenantId,
            'name' => substr($fallbackName, 0, 255),
            'document' => $fallbackDocument !== null ? substr($fallbackDocument, 0, 30) : null,
            'email' => $clientEmail !== '' ? substr($clientEmail, 0, 255) : null,
            'phone' => $clientPhone !== '' ? substr($clientPhone, 0, 40) : null,
            'billing_contact' => 'Importado via Printwayy',
        ]);

        return (int) $created->id;
    }

    private function resolvePrinterExternalId(array $snapshot): ?string
    {
        $value = $this->pickFirst($snapshot, ['id', 'printer_id', 'printerId', 'equipment_id', 'equipmentId']);

        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function resolveLocationLabel(array $snapshot): ?string
    {
        $location = $this->pickFirst($snapshot, [
            'location.address.name',
            'installationPoint',
            'location.customerName',
            'location.businessName',
            'location.address.street',
            'location',
            'department',
            'site',
        ]);

        if ($location === null || $location === '') {
            return null;
        }

        if (is_string($location) || is_numeric($location)) {
            return substr(trim((string) $location), 0, 255);
        }

        if (is_array($location)) {
            $parts = array_filter([
                Arr::get($location, 'address.name'),
                Arr::get($location, 'customerName'),
                Arr::get($location, 'businessName'),
                Arr::get($location, 'address.street'),
                Arr::get($location, 'department'),
            ], fn ($part) => is_string($part) && trim($part) !== '');

            if ($parts !== []) {
                return substr((string) implode(' - ', array_unique(array_map('trim', $parts))), 0, 255);
            }
        }

        return null;
    }

    private function extractCounterReadFromPayload(array $counters, array $snapshot): ?array
    {
        if ($counters === []) {
            return null;
        }

        $monoTotal = 0;
        $colorTotal = 0;
        $hasMono = false;
        $hasColor = false;
        $fallbackTotal = null;
        $latestReadAt = null;

        foreach ($counters as $counter) {
            if (! is_array($counter)) {
                continue;
            }

            $totalCount = $this->pickFirst($counter, ['totalCount', 'total', 'count']);

            if (! is_numeric($totalCount)) {
                continue;
            }

            $countValue = max((int) $totalCount, 0);
            $typeRaw = strtolower(trim((string) $this->pickFirst($counter, ['type', 'counterType', 'name'], 'unknown')));

            $normalizedType = str_replace(['-', '_', ' '], '', $typeRaw);

            if (in_array($normalizedType, ['blackandwhite', 'bw', 'mono', 'monochrome', 'blackwhite'], true)) {
                $monoTotal = max($monoTotal, $countValue);
                $hasMono = true;
            } elseif (in_array($normalizedType, ['color', 'colour', 'fullcolor', 'fullcolour'], true)) {
                $colorTotal = max($colorTotal, $countValue);
                $hasColor = true;
            } elseif (in_array($normalizedType, ['total', 'overall', 'allpages'], true)) {
                $fallbackTotal = max((int) ($fallbackTotal ?? 0), $countValue);
            }

            $counterReadAt = $this->normalizeDateTimeValue($this->pickFirst($counter, ['dateOfCapture', 'capturedAt', 'readAt', 'timestamp']));

            if ($counterReadAt !== null) {
                $latestReadAt = $counterReadAt;
            }
        }

        if (! $hasMono && ! $hasColor && $fallbackTotal === null) {
            return null;
        }

        if (! $hasMono && $fallbackTotal !== null) {
            $monoTotal = $fallbackTotal;
            $hasMono = true;
        }

        if (! $hasMono) {
            $monoTotal = 0;
        }

        if (! $hasColor) {
            $colorTotal = 0;
        }

        return [
            'mono_total' => $monoTotal,
            'color_total' => $colorTotal,
            'read_at' => $latestReadAt
                ?? $this->normalizeDateTimeValue($this->pickFirst($snapshot, ['lastCommunication', 'timestamp', 'updatedAt']))
                ?? now()->toDateTimeString(),
        ];
    }

    private function resolveOptionalTenantId(int $tenantId, string $table, int $id): ?int
    {
        if ($id < 1) {
            return null;
        }

        $exists = DB::table($table)
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->exists();

        return $exists ? $id : null;
    }

    private function resolveTicketClientId(int $tenantId, array $payload, ?Equipment $equipment): int
    {
        $clientId = (int) ($this->pickFirst($payload, ['client_id'], 0) ?: ($equipment?->client_id ?? 0));

        if ($clientId > 0) {
            return $clientId;
        }

        return (int) ($this->resolveClientId($tenantId, $payload) ?? 0);
    }

    private function normalizePriority(string $priority): string
    {
        $normalized = strtolower($priority);

        return in_array($normalized, ['low', 'medium', 'high', 'critical'], true) ? $normalized : 'medium';
    }

    private function normalizeTicketStatus(string $status): string
    {
        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'new', 'open', 'opened', 'pending' => TicketStatus::OPEN->value,
            'triage', 'analysis' => TicketStatus::TRIAGE->value,
            'assigned', 'dispatched' => TicketStatus::DISPATCHED->value,
            'in_progress', 'in-progress', 'working', 'ongoing' => TicketStatus::IN_PROGRESS->value,
            'resolved', 'done', 'fixed' => TicketStatus::RESOLVED->value,
            'closed', 'canceled', 'cancelled' => TicketStatus::CLOSED->value,
            default => TicketStatus::OPEN->value,
        };
    }

    private function resolveExternalReference(array $payload): ?string
    {
        $value = $this->pickFirst($payload, ['external_reference', 'ticket_id', 'alert_id', 'id', 'uuid', 'external_id']);

        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        $reference = trim((string) $value);

        return $reference !== '' ? $reference : null;
    }

    private function buildExternalReference(string $source, array $payload, ?string $serial = null, ?string $kind = null): string
    {
        $seed = implode('|', [
            $source,
            (string) ($serial ?? ''),
            (string) ($kind ?? ''),
            (string) $this->pickFirst($payload, ['message', 'description', 'title', 'subject'], ''),
            (string) $this->pickFirst($payload, ['timestamp', 'created_at', 'opened_at', 'read_at'], ''),
        ]);

        return hash('sha256', strtolower($seed));
    }

    private function payloadHash(array $payload): string
    {
        $normalized = Arr::sortRecursive($payload);
        $json = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return hash('sha256', (string) $json);
    }

    private function normalizeDateTimeValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value) || is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return null;
    }

    private function normalizeEquipmentStatus(string $status): string
    {
        return match (strtolower($status)) {
            'up', 'ok', 'ready', 'active', 'online' => 'online',
            'down', 'offline', 'unreachable' => 'offline',
            'warning', 'alert', 'toner_low', 'error' => 'alert',
            'maint', 'maintenance', 'repair' => 'maintenance',
            'retired', 'removed', 'decommissioned' => 'retired',
            default => 'online',
        };
    }

    private function normalizeListPayload(mixed $response): array
    {
        if (! is_array($response)) {
            return [];
        }

        if (array_is_list($response)) {
            return $response;
        }

        foreach (['data', 'items', 'equipment', 'alerts', 'tickets', 'results'] as $key) {
            $value = Arr::get($response, $key);

            if (is_array($value)) {
                return array_is_list($value) ? $value : array_values($value);
            }
        }

        return [];
    }

    private function fetchOffsetPaginatedPayload(Company $company, string $endpoint, int $batchSize = 100): array
    {
        $endpoint = trim($endpoint);

        if ($endpoint === '') {
            return [];
        }

        if (! str_starts_with($endpoint, '/')) {
            $endpoint = '/'.$endpoint;
        }

        $query = $this->tenantQueryParams($company);
        $response = $this->clientForCompany($company)->get($endpoint, $query)->throw()->json();
        $items = $this->normalizeListPayload($response);
        $total = max((int) Arr::get($response, 'count', count($items)), count($items));

        if ($items === [] || $total <= count($items)) {
            return $this->uniquePayloadByIdentity($items);
        }

        $allItems = $items;
        $offset = count($items);
        $previousSignature = $this->chunkSignature($items);
        $maxIterations = max((int) ceil($total / max($batchSize, 1)) + 3, 12);

        for ($i = 0; $i < $maxIterations && $offset < $total; $i++) {
            $pagedResponse = $this->clientForCompany($company)
                ->get($endpoint, array_merge($query, [
                    'skip' => $offset,
                    'take' => $batchSize,
                ]))
                ->throw()
                ->json();

            $chunk = $this->normalizeListPayload($pagedResponse);

            if ($chunk === []) {
                break;
            }

            $chunkSignature = $this->chunkSignature($chunk);

            if ($chunkSignature !== '' && $chunkSignature === $previousSignature) {
                break;
            }

            $allItems = array_merge($allItems, $chunk);
            $offset += count($chunk);
            $previousSignature = $chunkSignature;
        }

        return $this->uniquePayloadByIdentity($allItems);
    }

    private function chunkSignature(array $items): string
    {
        if ($items === []) {
            return '';
        }

        $first = (array) ($items[0] ?? []);
        $last = (array) ($items[count($items) - 1] ?? []);

        return implode('|', [
            (string) count($items),
            $this->resolvePayloadIdentity($first),
            $this->resolvePayloadIdentity($last),
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function uniquePayloadByIdentity(array $items): array
    {
        $seen = [];
        $unique = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $identity = $this->resolvePayloadIdentity($item);

            if (isset($seen[$identity])) {
                continue;
            }

            $seen[$identity] = true;
            $unique[] = $item;
        }

        return $unique;
    }

    private function resolvePayloadIdentity(array $payload): string
    {
        $identity = trim((string) $this->pickFirst($payload, [
            'id',
            'printer_id',
            'printerId',
            'equipment_id',
            'equipmentId',
            'serial_number',
            'serial',
            'serialNumber',
            'external_reference',
            'ticket_id',
            'alert_id',
        ], ''));

        if ($identity !== '') {
            return $identity;
        }

        return hash('sha256', json_encode(Arr::sortRecursive($payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
    }

    private function pickFirst(array $payload, array $keys, mixed $default = null): mixed
    {
        foreach ($keys as $key) {
            if (! Arr::has($payload, $key)) {
                continue;
            }

            $value = Arr::get($payload, $key);

            if ($value === null || $value === '') {
                continue;
            }

            return $value;
        }

        return $default;
    }

    private function hasAnyKey(array $payload, array $keys): bool
    {
        foreach ($keys as $key) {
            if (Arr::has($payload, $key)) {
                return true;
            }
        }

        return false;
    }
}
