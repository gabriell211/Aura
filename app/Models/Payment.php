<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends TenantAwareModel
{
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
