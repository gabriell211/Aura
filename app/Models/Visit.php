<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends TenantAwareModel
{
    protected function casts(): array
    {
        return [
            'checklist' => 'array',
            'photos' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
