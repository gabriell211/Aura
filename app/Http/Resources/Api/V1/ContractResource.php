<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'status' => $this->status,
            'client_id' => $this->client_id,
            'client_name' => $this->whenLoaded('client', fn () => $this->client?->name),
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'monthly_fee' => $this->monthly_fee,
            'included_bw_pages' => $this->included_bw_pages,
            'included_color_pages' => $this->included_color_pages,
            'bw_overage_price' => $this->bw_overage_price,
            'color_overage_price' => $this->color_overage_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
