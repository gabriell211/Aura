<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class GenerateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference' => ['nullable', 'regex:/^(19|20)\d{2}(0[1-9]|1[0-2])$/'],
            'emit_ticket_on_anomaly' => ['sometimes', 'boolean'],
        ];
    }
}
