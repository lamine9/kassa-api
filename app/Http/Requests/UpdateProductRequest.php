<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.update') ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;
        $productId = $this->route('product');

        return [
            'category_id' => [
                'sometimes',
                'nullable',
                'string',
                Rule::exists('categories', 'id')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],

            'unit_id' => [
                'sometimes',
                'required',
                'string',
                Rule::exists('units', 'id')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],

            'name' => [
                'sometimes',
                'required',
                'string',
                'max:200',
            ],

            'sku' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($productId),
            ],

            'barcode' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'barcode')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($productId),
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'purchase_price' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
            ],

            'selling_price' => [
                'sometimes',
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
