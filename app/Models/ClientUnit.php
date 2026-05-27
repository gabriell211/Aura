<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientUnit extends TenantAwareModel
{
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
