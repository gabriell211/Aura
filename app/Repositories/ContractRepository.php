<?php

namespace App\Repositories;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Collection;

class ContractRepository
{
    public function activeForTenant(int $tenantId): Collection
    {
        return Contract::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();
    }
}
