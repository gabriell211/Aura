<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentPartUsage extends TenantAwareModel
{
    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }
}
