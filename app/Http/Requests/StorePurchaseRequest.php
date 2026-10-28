<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('purchases.create') ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'supplier_id' => [
                'required',
                'string',
                Rule::exists('suppliers', 'id')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],

            'supplier_reference' => [
                'nullable',
                'string',
                'max:100',
            ],

            'purchase_date' => [
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
                'nullable',
                'string',
            ],

            'items' => [
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
