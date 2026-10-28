<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierReturnResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,

            'status' => $this->status->value,

            'return_date' => $this->return_date?->toDateString(),

            'purchase' => [
                'id' => $this->purchase?->id,
                'reference' => $this->purchase?->reference,
            ],

            'supplier' => [
                'id' => $this->supplier?->id,
                'name' => $this->supplier?->name,
            ],

            'items' => SupplierReturnItemResource::collection(
                $this->whenLoaded('items')
            ),

            'subtotal' => (float) $this->subtotal,

            'reason' => $this->reason,
            'note' => $this->note,

            'creator' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ],

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
