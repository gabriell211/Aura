<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentLog extends TenantAwareModel
{
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'happened_at' => 'datetime',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
