<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StartTrialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $billingBankKeys = array_keys((array) config('aura.billing.banks', [
            'bb' => 'Banco do Brasil',
            'itau' => 'Itau',
            'bradesco' => 'Bradesco',
            'santander' => 'Santander',
            'caixa' => 'Caixa Economica Federal',
            'inter' => 'Inter',
            'sicredi' => 'Sicredi',
            'sicoob' => 'Sicoob',
            'c6' => 'C6 Bank',
            'outro' => 'Outro banco',
        ]));

        return [
            'company_legal_name' => ['required', 'string', 'max:255'],
            'company_trade_name' => ['nullable', 'string', 'max:255'],
            'company_document' => ['nullable', 'string', 'max:30'],
            'company_phone' => ['nullable', 'string', 'max:40'],
            'monitoring_choice' => ['required', Rule::in(['printwayy', 'other'])],
            'printwayy_api_token' => ['nullable', 'string', 'max:4096', 'required_if:monitoring_choice,printwayy'],
            'printwayy_api_base_url' => ['nullable', 'url', 'max:255'],
            'billing_bank' => ['required', Rule::in($billingBankKeys)],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
            'accept_terms' => ['required', 'accepted'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_legal_name' => trim((string) $this->input('company_legal_name')),
            'company_trade_name' => trim((string) $this->input('company_trade_name')),
            'company_document' => trim((string) $this->input('company_document')),
            'company_phone' => trim((string) $this->input('company_phone')),
            'monitoring_choice' => trim((string) $this->input('monitoring_choice')),
            'printwayy_api_token' => trim((string) $this->input('printwayy_api_token')),
            'printwayy_api_base_url' => trim((string) $this->input('printwayy_api_base_url')),
            'billing_bank' => trim((string) $this->input('billing_bank')),
            'admin_name' => trim((string) $this->input('admin_name')),
            'admin_email' => Str::lower(trim((string) $this->input('admin_email'))),
        ]);
    }

    public function attributes(): array
    {
        return [
            'company_legal_name' => 'razao social',
            'company_trade_name' => 'nome fantasia',
            'company_document' => 'documento',
            'company_phone' => 'telefone',
            'monitoring_choice' => 'integracao de monitoramento',
            'printwayy_api_token' => 'token da Printwayy',
            'printwayy_api_base_url' => 'URL base da Printwayy',
            'billing_bank' => 'banco de faturamento',
            'admin_name' => 'nome do responsavel',
            'admin_email' => 'email corporativo',
            'accept_terms' => 'termos',
        ];
    }
}
