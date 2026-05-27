<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreEquipmentRequest;
use App\Http\Requests\Api\V1\UpdateEquipmentRequest;
use App\Http\Resources\Api\V1\EquipmentResource;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends ApiController
{
    public function index(Request $request)
    {
        $status = (string) $request->string('status');
        $contractId = $request->integer('contract_id');

        $equipment = Equipment::query()
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($contractId > 0, fn ($query) => $query->where('contract_id', $contractId))
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return EquipmentResource::collection($equipment);
    }

    public function store(StoreEquipmentRequest $request)
    {
        $equipment = Equipment::query()->create($request->validated());

        return (new EquipmentResource($equipment))->response()->setStatusCode(201);
    }

    public function show(Equipment $equipment): EquipmentResource
    {
        return new EquipmentResource($equipment);
    }

    public function update(UpdateEquipmentRequest $request, Equipment $equipment): EquipmentResource
    {
        $equipment->fill($request->validated());
        $equipment->save();

        return new EquipmentResource($equipment->refresh());
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->delete();

        return response()->noContent();
    }
}
