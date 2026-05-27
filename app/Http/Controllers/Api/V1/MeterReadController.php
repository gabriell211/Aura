<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\MeterReadData;
use App\Http\Requests\Api\V1\StoreMeterReadRequest;
use App\Http\Resources\Api\V1\MeterReadResource;
use App\Services\MeterReadService;

class MeterReadController extends ApiController
{
    public function __construct(private readonly MeterReadService $meterReadService)
    {
    }

    public function store(StoreMeterReadRequest $request)
    {
        $payload = $request->validated();

        $meterRead = $this->meterReadService->register(new MeterReadData(
            tenantId: (int) app('tenant_id'),
            equipmentId: (int) $payload['equipment_id'],
            readAt: (string) ($payload['read_at'] ?? now()->toDateTimeString()),
            monoTotal: (int) $payload['mono_total'],
            colorTotal: (int) $payload['color_total'],
            source: (string) ($payload['source'] ?? 'manual'),
            rawPayload: (array) ($payload['raw_payload'] ?? []),
        ));

        return (new MeterReadResource($meterRead))->response()->setStatusCode(201);
    }
}
