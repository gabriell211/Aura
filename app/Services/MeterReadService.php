<?php

namespace App\Services;

use App\DTOs\MeterReadData;
use App\Jobs\ProcessMeterReadJob;
use App\Models\MeterRead;
use App\Repositories\MeterReadRepository;
use Illuminate\Validation\ValidationException;

class MeterReadService
{
    public function __construct(private readonly MeterReadRepository $meterReadRepository)
    {
    }

    public function register(MeterReadData $data): MeterRead
    {
        $latestRead = $this->meterReadRepository->latestForEquipment($data->tenantId, $data->equipmentId);

        if ($latestRead !== null && ($data->monoTotal < (int) $latestRead->mono_total || $data->colorTotal < (int) $latestRead->color_total)) {
            throw ValidationException::withMessages([
                'meter_totals' => 'Counter totals cannot decrease compared to the latest reading.',
            ]);
        }

        $meterRead = $this->meterReadRepository->create([
            'tenant_id' => $data->tenantId,
            'equipment_id' => $data->equipmentId,
            'read_at' => $data->readAt,
            'mono_total' => $data->monoTotal,
            'color_total' => $data->colorTotal,
            'source' => $data->source,
            'raw_payload' => $data->rawPayload,
        ]);

        ProcessMeterReadJob::dispatch($meterRead->id);

        return $meterRead;
    }
}
