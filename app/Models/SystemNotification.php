<?php

namespace App\Models;

class SystemNotification extends TenantAwareModel
{
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }
}
