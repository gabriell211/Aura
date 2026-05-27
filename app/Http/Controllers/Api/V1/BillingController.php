<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\GenerateInvoiceRequest;
use App\Http\Resources\Api\V1\InvoiceResource;
use App\Http\Resources\Api\V1\TicketResource;
use App\Models\Contract;
use App\Models\Invoice;
use App\Services\BillingService;
use App\Services\TicketAutomationService;

class BillingController extends ApiController
{
    public function __construct(
        private readonly BillingService $billingService,
        private readonly TicketAutomationService $ticketAutomationService,
    ) {
    }

    public function index()
    {
        $invoices = Invoice::query()->with('items')->latest()->paginate(15);

        return InvoiceResource::collection($invoices);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($invoice->load('items'));
    }

    public function generate(GenerateInvoiceRequest $request, Contract $contract)
    {
        $reference = (string) ($request->validated()['reference'] ?? now()->format((string) config('aura.billing.reference_format', 'Ym')));

        $result = $this->billingService->generateMonthlyInvoice($contract, $reference);

        $ticket = null;

        if (($request->boolean('emit_ticket_on_anomaly') || $result->anomalyDetected) && $result->anomalyDetected) {
            $ticket = $this->ticketAutomationService->openForAlert([
                'tenant_id' => (int) $contract->tenant_id,
                'client_id' => (int) $contract->client_id,
                'title' => sprintf('Consumo acima do previsto (%s)', $reference),
                'description' => sprintf(
                    'Uso P&B: %d (excedente %d), Uso colorido: %d (excedente %d).',
                    $result->bwUsagePages,
                    $result->bwOveragePages,
                    $result->colorUsagePages,
                    $result->colorOveragePages,
                ),
                'priority' => 'high',
                'origin' => 'billing',
            ]);
        }

        return $this->success([
            'invoice_id' => $result->invoiceId,
            'reference' => $result->reference,
            'subtotal' => $result->subtotal,
            'excess_total' => $result->excessTotal,
            'total' => $result->grandTotal,
            'usage' => [
                'bw_pages' => $result->bwUsagePages,
                'color_pages' => $result->colorUsagePages,
                'bw_overage_pages' => $result->bwOveragePages,
                'color_overage_pages' => $result->colorOveragePages,
                'anomaly_detected' => $result->anomalyDetected,
            ],
            'ticket' => $ticket ? (new TicketResource($ticket))->toArray($request) : null,
        ]);
    }
}
