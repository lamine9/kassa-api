<?php

namespace App\Http\Requests;

use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('units.create') ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('units', 'name')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],

            'symbol' => [
                'required',
                'string',
                'max:20',
                Rule::unique('units', 'symbol')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],

            'decimal_places' => [
                'required',
                'integer',
                'min:0',
                'max:6',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
