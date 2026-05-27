<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMeterReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = (int) app('tenant_id');

        return [
            'equipment_id' => [
                'required',
                Rule::exists('equipment', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'read_at' => ['nullable', 'date'],
            'mono_total' => ['required', 'integer', 'min:0'],
            'color_total' => ['required', 'integer', 'min:0'],
            'source' => ['sometimes', Rule::in((array) config('aura.meter_read_sources', []))],
            'raw_payload' => ['sometimes', 'array'],
        ];
    }
}
