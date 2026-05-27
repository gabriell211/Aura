<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreClientRequest;
use App\Http\Requests\Api\V1\UpdateClientRequest;
use App\Http\Resources\Api\V1\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends ApiController
{
    public function index(Request $request)
    {
        $search = (string) $request->string('q');

        $clients = Client::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return ClientResource::collection($clients);
    }

    public function store(StoreClientRequest $request)
    {
        $client = Client::query()->create($request->validated());

        return (new ClientResource($client))->response()->setStatusCode(201);
    }

    public function show(Client $client): ClientResource
    {
        return new ClientResource($client);
    }

    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        $client->fill($request->validated());
        $client->save();

        return new ClientResource($client->refresh());
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return response()->noContent();
    }
}
