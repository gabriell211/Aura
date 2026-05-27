<?php

namespace App\Jobs;

use App\Models\Contract;
use App\Services\BillingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    public function __construct(public readonly int $contractId, public readonly string $reference)
    {
    }

    public function handle(BillingService $billingService): void
    {
        $contract = Contract::query()->find($this->contractId);

        if ($contract === null) {
            return;
        }

        app()->instance('tenant_id', (int) $contract->tenant_id);

        $billingService->generateMonthlyInvoice($contract, $this->reference);
    }
}
