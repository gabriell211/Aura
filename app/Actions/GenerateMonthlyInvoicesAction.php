<?php

namespace App\Actions;

use App\Repositories\ContractRepository;
use App\Services\BillingService;

class GenerateMonthlyInvoicesAction
{
    public function __construct(
        private readonly BillingService $billingService,
        private readonly ContractRepository $contractRepository,
    ) {
    }

    public function execute(int $tenantId, ?string $reference = null): int
    {
        app()->instance('tenant_id', $tenantId);

        $reference ??= now()->format((string) config('aura.billing.reference_format', 'Ym'));
        $processed = 0;

        $contracts = $this->contractRepository->activeForTenant($tenantId);

        foreach ($contracts as $contract) {
            $this->billingService->generateMonthlyInvoice($contract, $reference);
            $processed++;
        }

        return $processed;
    }
}
