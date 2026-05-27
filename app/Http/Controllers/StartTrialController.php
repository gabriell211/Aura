<?php

namespace App\Http\Controllers;

use App\Http\Requests\Web\StartTrialRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StartTrialController extends Controller
{
    public function create(): View
    {
        return view('trial.create', [
            'billingBanks' => (array) config('aura.billing.banks', []),
        ]);
    }

    public function store(StartTrialRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $trialStartsAt = now();
        $trialEndsAt = now()->addDays(30);
        $equipmentLimit = (int) config('aura.plans.start.equipment_limit', 200);
        $usesPrintwayy = (($payload['monitoring_choice'] ?? 'other') === 'printwayy');
        $printwayyBaseUrl = $usesPrintwayy
            ? (string) (($payload['printwayy_api_base_url'] ?? '') ?: config('services.printwayy.api_base_url', ''))
            : null;

        [$company, $user] = DB::transaction(function () use ($payload, $trialStartsAt, $trialEndsAt, $equipmentLimit, $usesPrintwayy, $printwayyBaseUrl): array {
            $company = Company::query()->create([
                'uuid' => (string) Str::uuid(),
                'legal_name' => (string) $payload['company_legal_name'],
                'trade_name' => filled($payload['company_trade_name'] ?? null) ? (string) $payload['company_trade_name'] : null,
                'document' => filled($payload['company_document'] ?? null) ? (string) $payload['company_document'] : null,
                'email' => (string) $payload['admin_email'],
                'phone' => filled($payload['company_phone'] ?? null) ? (string) $payload['company_phone'] : null,
                'plan' => 'start',
                'equipment_limit' => $equipmentLimit,
                'billing_bank' => (string) $payload['billing_bank'],
                'is_active' => true,
                'printwayy_enabled' => $usesPrintwayy,
                'printwayy_api_base_url' => $usesPrintwayy && filled($printwayyBaseUrl) ? $printwayyBaseUrl : null,
                'printwayy_api_token' => $usesPrintwayy ? (string) $payload['printwayy_api_token'] : null,
                'trial_starts_at' => $trialStartsAt,
                'trial_ends_at' => $trialEndsAt,
                'trial_status' => 'trialing',
            ]);

            $user = User::query()->create([
                'name' => (string) $payload['admin_name'],
                'email' => (string) $payload['admin_email'],
                'password' => (string) $payload['password'],
                'company_id' => (int) $company->id,
                'tenant_id' => (int) $company->id,
                'role' => 'admin',
            ]);

            $company->forceFill([
                'created_by' => (int) $user->id,
                'updated_by' => (int) $user->id,
            ])->save();

            return [$company, $user];
        });

        return redirect()
            ->route('trial.create')
            ->with('trial_success', sprintf(
                'Teste ativado para %s ate %s.',
                $company->trade_name ?: $company->legal_name,
                $company->trial_ends_at?->format('d/m/Y')
            ))
            ->with('trial_admin_email', $user->email)
            ->with('trial_access_url', url('/admin/login'));
    }
}
