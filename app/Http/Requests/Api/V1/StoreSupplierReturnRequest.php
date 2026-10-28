<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'purchase_id' => [
                'required',
                'string',
                Rule::exists('purchases', 'id')
                    ->where('tenant_id', $tenantId),
            ],

            'return_date' => [
                'required',
                'date',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'note' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.purchase_item_id' => [
                'required',
                'string',
                'distinct',
                Rule::exists('purchase_items', 'id')
                    ->where('tenant_id', $tenantId),
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'purchase_id.required' => 'L’achat est obligatoire.',
            'purchase_id.exists' => 'L’achat sélectionné est introuvable.',

            'return_date.required' => 'La date du retour est obligatoire.',
            'return_date.date' => 'La date du retour est invalide.',

            'items.required' => 'Le retour doit contenir au moins un article.',
            'items.min' => 'Le retour doit contenir au moins un article.',

            'items.*.purchase_item_id.required' => 'La ligne d’achat est obligatoire.',
            'items.*.purchase_item_id.distinct' => 'Une même ligne d’achat ne peut apparaître deux fois.',
            'items.*.purchase_item_id.exists' => 'La ligne d’achat sélectionnée est introuvable.',

            'items.*.quantity.required' => 'La quantité est obligatoire.',
            'items.*.quantity.numeric' => 'La quantité doit être numérique.',
            'items.*.quantity.gt' => 'La quantité doit être supérieure à zéro.',
        ];
    }
}
