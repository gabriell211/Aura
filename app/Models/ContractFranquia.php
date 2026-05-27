<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractFranquia extends TenantAwareModel
{
    protected $table = 'contract_franquias';

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
