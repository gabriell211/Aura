<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractItem extends TenantAwareModel
{
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
