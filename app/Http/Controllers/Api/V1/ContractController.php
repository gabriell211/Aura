<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreContractRequest;
use App\Http\Requests\Api\V1\UpdateContractRequest;
use App\Http\Resources\Api\V1\ContractResource;
use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends ApiController
{
    public function index(Request $request)
    {
        $status = (string) $request->string('status');

        $contracts = Contract::query()
            ->with('client:id,name')
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return ContractResource::collection($contracts);
    }

    public function store(StoreContractRequest $request)
    {
        $contract = Contract::query()->create($request->validated());

        return (new ContractResource($contract->load('client:id,name')))->response()->setStatusCode(201);
    }

    public function show(Contract $contract): ContractResource
    {
        return new ContractResource($contract->load('client:id,name'));
    }

    public function update(UpdateContractRequest $request, Contract $contract): ContractResource
    {
        $contract->fill($request->validated());
        $contract->save();

        return new ContractResource($contract->refresh()->load('client:id,name'));
    }

    public function destroy(Contract $contract)
    {
        $contract->delete();

        return response()->noContent();
    }
}
