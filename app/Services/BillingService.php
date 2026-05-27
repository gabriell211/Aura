<?php

namespace App\Services;

use App\DTOs\InvoiceGenerationResult;
use App\Models\Contract;
use App\Repositories\InvoiceRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillingService
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly ContractUsageService $contractUsageService,
    ) {
    }

    public function generateMonthlyInvoice(Contract $contract, string $reference): InvoiceGenerationResult
    {
        $periodReference = CarbonImmutable::createFromFormat('Ym', $reference);

        if ($periodReference === false) {
            throw ValidationException::withMessages([
                'reference' => 'Invalid billing reference format. Expected YYYYMM.',
            ]);
        }

        $periodStart = $periodReference->startOfMonth();
        $periodEnd = $periodStart->endOfMonth();
        $usageSummary = $this->contractUsageService->summarizeForPeriod($contract, $periodStart, $periodEnd);

        $subtotal = (float) $contract->monthly_fee;
        $excessTotal = (float) $usageSummary['excess_total'];
        $grandTotal = round($subtotal + $excessTotal, 2);
        $dueDays = (int) config('aura.billing.default_due_days', 10);
        $dueDate = $periodEnd->addDays($dueDays)->toDateString();

        $invoice = DB::transaction(function () use (
            $contract,
            $reference,
            $periodStart,
            $periodEnd,
            $dueDate,
            $subtotal,
            $excessTotal,
            $grandTotal,
            $usageSummary
        ) {
            $existingInvoice = $this->invoiceRepository->findByContractAndReference($contract->id, $reference);

            $payload = [
                'tenant_id' => $contract->tenant_id,
                'client_id' => $contract->client_id,
                'contract_id' => $contract->id,
                'billing_reference' => $reference,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'due_date' => $dueDate,
                'subtotal' => $subtotal,
                'excess_total' => $excessTotal,
                'total' => $grandTotal,
                'status' => 'draft',
            ];

            $invoice = $existingInvoice === null
                ? $this->invoiceRepository->create($payload)
                : $this->invoiceRepository->update($existingInvoice, $payload);

            $invoiceItems = [
                [
                    'item_type' => 'monthly_fee',
                    'description' => sprintf('Mensalidade contrato %s', $contract->code),
                    'quantity' => 1,
                    'unit_price' => $subtotal,
                    'line_total' => $subtotal,
                ],
            ];

            if ((int) $usageSummary['bw_overage_pages'] > 0) {
                $invoiceItems[] = [
                    'item_type' => 'bw_overage',
                    'description' => 'Excedente P&B',
                    'quantity' => (int) $usageSummary['bw_overage_pages'],
                    'unit_price' => (float) $contract->bw_overage_price,
                    'line_total' => (float) $usageSummary['bw_overage_total'],
                ];
            }

            if ((int) $usageSummary['color_overage_pages'] > 0) {
                $invoiceItems[] = [
                    'item_type' => 'color_overage',
                    'description' => 'Excedente colorido',
                    'quantity' => (int) $usageSummary['color_overage_pages'],
                    'unit_price' => (float) $contract->color_overage_price,
                    'line_total' => (float) $usageSummary['color_overage_total'],
                ];
            }

            $this->invoiceRepository->replaceItems($invoice, $invoiceItems);

            return $invoice;
        });

        return new InvoiceGenerationResult(
            invoiceId: $invoice->id,
            reference: $reference,
            subtotal: $subtotal,
            excessTotal: $excessTotal,
            grandTotal: $grandTotal,
            bwUsagePages: (int) $usageSummary['bw_usage_pages'],
            colorUsagePages: (int) $usageSummary['color_usage_pages'],
            bwOveragePages: (int) $usageSummary['bw_overage_pages'],
            colorOveragePages: (int) $usageSummary['color_overage_pages'],
            anomalyDetected: (bool) $usageSummary['anomaly_detected'],
        );
    }
}
