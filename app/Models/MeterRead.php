<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterRead extends TenantAwareModel
{
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
