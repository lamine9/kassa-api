<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('purchases.update') ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'supplier_id' => [
                'sometimes',
                'required',
                'string',
                Rule::exists('suppliers', 'id')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],

            'supplier_reference' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'purchase_date' => [
                'sometimes',
                'required',
                'date',
            ],

            'discount' => [
                'sometimes',
                'numeric',
                'min:0',
            ],

            'tax' => [
                'sometimes',
                'numeric',
                'min:0',
            ],

            'note' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'items' => [
                'sometimes',
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'string',
                'distinct',
                Rule::exists('products', 'id')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,3',
            ],

            'items.*.unit_cost' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],

            'items.*.discount' => [
                'sometimes',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],

            'items.*.tax' => [
                'sometimes',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
        ];
    }
}
