<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\EquipmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = (int) app('tenant_id');
        $equipmentId = (int) $this->route('equipment')->id;

        return [
            'client_id' => [
                'sometimes',
                'required',
                Rule::exists('clients', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'client_unit_id' => [
                'sometimes',
                'nullable',
                Rule::exists('client_units', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'contract_id' => [
                'sometimes',
                'nullable',
                Rule::exists('contracts', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'manufacturer' => ['sometimes', 'required', 'string', 'max:120'],
            'model' => ['sometimes', 'required', 'string', 'max:120'],
            'serial_number' => [
                'sometimes',
                'required',
                'string',
                'max:120',
                Rule::unique('equipment', 'serial_number')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($equipmentId),
            ],
            'ip_address' => ['sometimes', 'nullable', 'ip'],
            'mac_address' => ['sometimes', 'nullable', 'string', 'max:40'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::enum(EquipmentStatus::class)],
            'installed_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
