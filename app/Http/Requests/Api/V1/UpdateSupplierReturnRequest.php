<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'return_date' => [
                'sometimes',
                'required',
                'date',
            ],

            'reason' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],

            'note' => [
                'sometimes',
                'nullable',
                'string',
                'max:2000',
            ],

            'items' => [
                'sometimes',
                'required',
                'array',
                'min:1',
            ],

            'items.*.purchase_item_id' => [
                'required_with:items',
                'string',
                'distinct',
                Rule::exists('purchase_items', 'id')
                    ->where('tenant_id', $tenantId),
            ],

            'items.*.quantity' => [
                'required_with:items',
                'numeric',
                'gt:0',
            ],
        ];
    }
}
