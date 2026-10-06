<?php

namespace App\Http\Requests;

use App\Enums\StockMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('stock.create') ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'product_id' => [
                'required',
                'string',
                Rule::exists('products', 'id')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],

            'type' => [
                'required',
                Rule::enum(StockMovementType::class),
            ],

            'quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'unit_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'reference_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'reference_id' => [
                'nullable',
                'string',
                'max:26',
            ],

            'note' => [
                'nullable',
                'string',
            ],
        ];
    }
}
