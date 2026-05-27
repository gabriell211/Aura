<?php

namespace App\Filament\Concerns;

use App\Services\PrintwayyIntegrationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Throwable;

trait HandlesPrintwayySync
{
    protected function getPrintwayySyncAction(string $name = 'syncPrintwayy'): Action
    {
        return Action::make($name)
            ->label('Sincronizar Printwayy')
            ->icon('heroicon-o-arrow-path')
            ->color('info')
            ->requiresConfirmation()
            ->action(function (): void {
                $tenantId = (int) (Auth::user()?->tenant_id ?? 0);

                if ($tenantId < 1) {
                    Notification::make()
                        ->danger()
                        ->title('Não foi possível identificar a empresa deste usuário.')
                        ->send();

                    return;
                }

                try {
                    $stats = app(PrintwayyIntegrationService::class)->syncTenant($tenantId);
                } catch (Throwable $exception) {
                    Notification::make()
                        ->danger()
                        ->title('Falha na sincronização com a Printwayy')
                        ->body($this->buildSyncErrorMessage($exception->getMessage()))
                        ->send();

                    return;
                }

                if (! (bool) ($stats['ok'] ?? false)) {
                    $reason = (string) ($stats['reason'] ?? 'erro_desconhecido');
                    $message = (string) ($stats['message'] ?? '');

                    Notification::make()
                        ->warning()
                        ->title('Sincronização não concluída')
                        ->body('Motivo: '.$reason.($message !== '' ? ' | '.$this->buildSyncErrorMessage($message) : ''))
                        ->send();

                    return;
                }

                Notification::make()
                    ->success()
                    ->title('Sincronização concluída')
                    ->body(sprintf(
                        'Equipamentos: %d processados | Leituras: %d | Alertas: %d | Chamados externos: %d',
                        (int) ($stats['equipment_processed'] ?? 0),
                        (int) ($stats['meter_reads_created'] ?? 0),
                        (int) ($stats['alerts_processed'] ?? 0),
                        (int) ($stats['external_tickets_synced'] ?? 0),
                    ))
                    ->send();
            });
    }

    protected function buildSyncErrorMessage(string $rawMessage): string
    {
        if (str_contains($rawMessage, 'cURL error 60')) {
            return 'Erro SSL na conexão com a Printwayy. Verifique o certificado CA do servidor PHP ou, em ambiente local, defina PRINTWAYY_VERIFY_SSL=false no .env.';
        }

        return $rawMessage;
    }
}
