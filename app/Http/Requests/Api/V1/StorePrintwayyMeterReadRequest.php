<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePrintwayyMeterReadRequest extends FormRequest
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
                'nullable',
                Rule::exists('equipment', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'read_at' => ['nullable', 'date'],
            'mono_total' => ['required', 'integer', 'min:0'],
            'color_total' => ['required', 'integer', 'min:0'],
            'raw_payload' => ['sometimes', 'array'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            if (! $this->filled('equipment_id') && ! $this->filled('serial_number')) {
                $validator->errors()->add('equipment_id', 'equipment_id or serial_number is required.');
            }
        }];
    }
}
