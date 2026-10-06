<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('units.update') ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;
        $unitId = $this->route('unit');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('units', 'name')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($unitId),
            ],

            'symbol' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('units', 'symbol')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($unitId),
            ],

            'decimal_places' => [
                'sometimes',
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
