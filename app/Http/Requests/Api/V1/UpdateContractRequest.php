<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ContractType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = (int) app('tenant_id');
        $contractId = (int) $this->route('contract')->id;

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
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('contracts', 'code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($contractId),
            ],
            'type' => ['sometimes', 'required', Rule::enum(ContractType::class)],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'monthly_fee' => ['sometimes', 'required', 'numeric', 'min:0'],
            'included_bw_pages' => ['sometimes', 'required', 'integer', 'min:0'],
            'included_color_pages' => ['sometimes', 'required', 'integer', 'min:0'],
            'bw_overage_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'color_overage_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'max:40'],
        ];
    }
}
