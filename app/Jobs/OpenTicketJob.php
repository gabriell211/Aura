<?php

namespace App\Jobs;

use App\Services\TicketAutomationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class OpenTicketJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    public function __construct(public readonly array $payload)
    {
    }

    public function handle(TicketAutomationService $ticketAutomationService): void
    {
        if (! isset($this->payload['tenant_id'])) {
            return;
        }

        app()->instance('tenant_id', (int) $this->payload['tenant_id']);

        $ticketAutomationService->openForAlert($this->payload);
    }
}
