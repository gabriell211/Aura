<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends TenantAwareModel
{
    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(ClientUnit::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
