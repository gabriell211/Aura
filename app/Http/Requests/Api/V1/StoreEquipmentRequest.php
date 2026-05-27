<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\EquipmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentRequest extends FormRequest
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
                'required',
                Rule::exists('clients', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'client_unit_id' => [
                'nullable',
                Rule::exists('client_units', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'contract_id' => [
                'nullable',
                Rule::exists('contracts', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'manufacturer' => ['required', 'string', 'max:120'],
            'model' => ['required', 'string', 'max:120'],
            'serial_number' => [
                'required',
                'string',
                'max:120',
                Rule::unique('equipment', 'serial_number')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'ip_address' => ['nullable', 'ip'],
            'mac_address' => ['nullable', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::enum(EquipmentStatus::class)],
            'installed_at' => ['nullable', 'date'],
        ];
    }
}
