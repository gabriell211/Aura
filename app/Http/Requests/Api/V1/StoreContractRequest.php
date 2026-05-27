<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ContractType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContractRequest extends FormRequest
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
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('contracts', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'type' => ['required', Rule::enum(ContractType::class)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'included_bw_pages' => ['required', 'integer', 'min:0'],
            'included_color_pages' => ['required', 'integer', 'min:0'],
            'bw_overage_price' => ['required', 'numeric', 'min:0'],
            'color_overage_price' => ['required', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'max:40'],
        ];
    }
}
