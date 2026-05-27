<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePrintwayyAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = (int) app('tenant_id');

        return [
            'client_id' => [
                'nullable',
                Rule::exists('clients', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'equipment_id' => [
                'nullable',
                Rule::exists('equipment', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'alert_type' => ['required', 'string', 'max:80'],
            'severity' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'message' => ['required', 'string', 'max:2000'],
            'title' => ['sometimes', 'string', 'max:255'],
            'source' => ['sometimes', 'string', 'max:40'],
        ];
    }
}
