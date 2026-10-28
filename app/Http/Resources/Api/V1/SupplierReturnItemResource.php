<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierReturnItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'purchase_item_id' => $this->purchase_item_id,

            'product' => [
                'id' => $this->product?->id,
                'name' => $this->product?->name,
                'sku' => $this->product?->sku,
            ],

            'quantity' => (float) $this->quantity,
            'unit_cost' => (float) $this->unit_cost,
            'total' => (float) $this->total,

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
