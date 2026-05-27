<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'equipment_id' => $this->equipment_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'origin' => $this->origin,
            'external_source' => $this->external_source,
            'external_reference' => $this->external_reference,
            'opened_at' => $this->opened_at,
            'closed_at' => $this->closed_at,
            'created_at' => $this->created_at,
        ];
    }
}
