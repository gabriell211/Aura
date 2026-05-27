<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeterReadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'equipment_id' => $this->equipment_id,
            'read_at' => $this->read_at,
            'mono_total' => $this->mono_total,
            'color_total' => $this->color_total,
            'source' => $this->source,
            'created_at' => $this->created_at,
        ];
    }
}
