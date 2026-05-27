<?php

namespace App\Jobs;

use App\Models\MeterRead;
use App\Models\Ticket;
use App\Services\BillingService;
use App\Services\TicketAutomationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class ProcessMeterReadJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    public function __construct(public readonly int $meterReadId)
    {
    }

    public function handle(BillingService $billingService, TicketAutomationService $ticketAutomationService): void
    {
        $meterRead = MeterRead::query()
            ->with('equipment.contract')
            ->find($this->meterReadId);

        if ($meterRead === null || $meterRead->equipment?->contract === null) {
            return;
        }

        $contract = $meterRead->equipment->contract;
        $reference = $meterRead->read_at->format((string) config('aura.billing.reference_format', 'Ym'));

        app()->instance('tenant_id', (int) $meterRead->tenant_id);

        $result = $billingService->generateMonthlyInvoice($contract, $reference);

        if (! $result->anomalyDetected) {
            return;
        }

        $alreadyOpen = Ticket::query()
            ->where('tenant_id', $contract->tenant_id)
            ->where('client_id', $contract->client_id)
            ->where('origin', 'billing')
            ->whereNotIn('status', ['closed', 'resolved'])
            ->where('title', 'like', '%'.$reference.'%')
            ->exists();

        if ($alreadyOpen) {
            return;
        }

        $ticketAutomationService->openForAlert([
            'tenant_id' => (int) $contract->tenant_id,
            'client_id' => (int) $contract->client_id,
            'equipment_id' => $meterRead->equipment_id,
            'title' => sprintf('Consumo acima do previsto (%s)', $reference),
            'description' => sprintf(
                'Uso P&B: %d (excedente %d), uso colorido: %d (excedente %d).',
                $result->bwUsagePages,
                $result->bwOveragePages,
                $result->colorUsagePages,
                $result->colorOveragePages,
            ),
            'priority' => 'high',
            'origin' => 'billing',
        ]);
    }
}
