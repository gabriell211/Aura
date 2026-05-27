<?php

namespace App\Jobs;

use App\Services\PrintwayyIntegrationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class SyncEquipmentJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    public function __construct(public readonly int $tenantId)
    {
    }

    public function handle(PrintwayyIntegrationService $printwayyIntegrationService): void
    {
        $printwayyIntegrationService->syncTenant($this->tenantId);
    }
}
