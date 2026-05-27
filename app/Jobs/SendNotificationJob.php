<?php

namespace App\Jobs;

use App\Models\SystemNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    public function __construct(public readonly int $notificationId)
    {
    }

    public function handle(): void
    {
        $notification = SystemNotification::query()->find($this->notificationId);

        if ($notification === null) {
            return;
        }

        app()->instance('tenant_id', (int) $notification->tenant_id);

        // Placeholder for real provider integrations. For MVP, mark as sent.
        $notification->status = 'sent';
        $notification->sent_at = now();
        $notification->save();
    }
}
