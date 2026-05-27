<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrialSignupTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_and_admin_are_created_with_30_day_trial(): void
    {
        Carbon::setTestNow('2026-05-26 10:00:00');

        $response = $this->post('/iniciar-teste', [
            'monitoring_choice' => 'printwayy',
            'printwayy_api_token' => 'pw_token_abc_123',
            'billing_bank' => 'itau',
            'company_legal_name' => 'Print Prime Outsourcing LTDA',
            'company_trade_name' => 'Print Prime',
            'company_document' => '12.345.678/0001-99',
            'company_phone' => '+55 11 98888-0000',
            'admin_name' => 'Carla Silva',
            'admin_email' => 'carla@printprime.com.br',
            'password' => 'Secreta123',
            'password_confirmation' => 'Secreta123',
            'accept_terms' => '1',
        ]);

        $response->assertRedirect(route('trial.create'));
        $response->assertSessionHas('trial_success');

        $company = Company::query()->first();

        $this->assertNotNull($company);
        $this->assertSame('start', $company->plan);
        $this->assertSame('itau', $company->billing_bank);
        $this->assertTrue((bool) $company->printwayy_enabled);
        $this->assertSame('pw_token_abc_123', $company->printwayy_api_token);
        $this->assertSame('2026-06-25', $company->trial_ends_at?->toDateString());
        $this->assertSame('trialing', $company->trial_status);

        $admin = User::query()->where('email', 'carla@printprime.com.br')->first();

        $this->assertNotNull($admin);
        $this->assertSame($company->id, $admin->tenant_id);
        $this->assertSame($company->id, $admin->company_id);
        $this->assertSame('admin', $admin->role);
    }

    public function test_signup_requires_unique_admin_email(): void
    {
        User::query()->create([
            'name' => 'User A',
            'email' => 'duplicado@empresa.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $response = $this->from(route('trial.create'))->post('/iniciar-teste', [
            'monitoring_choice' => 'other',
            'billing_bank' => 'bb',
            'company_legal_name' => 'Nova Empresa LTDA',
            'admin_name' => 'Admin B',
            'admin_email' => 'duplicado@empresa.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'accept_terms' => '1',
        ]);

        $response->assertRedirect(route('trial.create'));
        $response->assertSessionHasErrors(['admin_email']);
    }

    public function test_printwayy_token_is_required_when_printwayy_is_selected(): void
    {
        $response = $this->from(route('trial.create'))->post('/iniciar-teste', [
            'monitoring_choice' => 'printwayy',
            'billing_bank' => 'bb',
            'company_legal_name' => 'Empresa X',
            'admin_name' => 'Admin X',
            'admin_email' => 'adminx@empresa.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'accept_terms' => '1',
        ]);

        $response->assertRedirect(route('trial.create'));
        $response->assertSessionHasErrors(['printwayy_api_token']);
    }
}
