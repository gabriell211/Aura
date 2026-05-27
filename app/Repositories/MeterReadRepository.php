<?php

namespace App\Repositories;

use App\Models\MeterRead;

class MeterReadRepository
{
    public function latestForEquipment(int $tenantId, int $equipmentId): ?MeterRead
    {
        return MeterRead::query()
            ->where('tenant_id', $tenantId)
            ->where('equipment_id', $equipmentId)
            ->orderByDesc('read_at')
            ->first();
    }

    public function create(array $payload): MeterRead
    {
        return MeterRead::query()->create($payload);
    }
}
