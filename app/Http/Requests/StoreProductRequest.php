<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.create') ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'category_id' => [
                'nullable',
                'string',
                Rule::exists('categories', 'id')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],

            'unit_id' => [
                'required',
                'string',
                Rule::exists('units', 'id')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],

            'name' => [
                'required',
                'string',
                'max:200',
            ],

            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],

            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'barcode')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'purchase_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'selling_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'min_stock' => [
                'sometimes',
                'numeric',
                'min:0',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
