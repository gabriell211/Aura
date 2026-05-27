<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StorePrintwayyAlertRequest;
use App\Http\Requests\Api\V1\StorePrintwayyMeterReadRequest;
use App\Http\Resources\Api\V1\MeterReadResource;
use App\Http\Resources\Api\V1\TicketResource;
use App\Services\PrintwayyIntegrationService;

class PrintwayyWebhookController extends ApiController
{
    public function __construct(
        private readonly PrintwayyIntegrationService $printwayyIntegrationService,
    ) {
    }

    public function meterRead(StorePrintwayyMeterReadRequest $request)
    {
        $meterRead = $this->printwayyIntegrationService->ingestMeterRead(
            tenantId: (int) app('tenant_id'),
            payload: (array) ($request->validated() + ['raw_payload' => $request->all()]),
            source: 'webhook',
        );

        return (new MeterReadResource($meterRead))->response()->setStatusCode(201);
    }

    public function alert(StorePrintwayyAlertRequest $request)
    {
        $ticket = $this->printwayyIntegrationService->ingestAlert(
            tenantId: (int) app('tenant_id'),
            payload: (array) $request->validated(),
        );

        return (new TicketResource($ticket))->response()->setStatusCode(201);
    }

    public function sync()
    {
        return $this->success(
            data: $this->printwayyIntegrationService->syncTenant((int) app('tenant_id')),
            status: 202,
        );
    }
}
