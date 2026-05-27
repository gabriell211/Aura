<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contract_id' => $this->contract_id,
            'client_id' => $this->client_id,
            'billing_reference' => $this->billing_reference,
            'period_start' => $this->period_start,
            'period_end' => $this->period_end,
            'due_date' => $this->due_date,
            'subtotal' => $this->subtotal,
            'excess_total' => $this->excess_total,
            'total' => $this->total,
            'status' => $this->status,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'item_type' => $item->item_type,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ])->values()),
            'created_at' => $this->created_at,
        ];
    }
}
