<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Services\TrialLifecycleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrialLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_trial_warning_and_generates_infinitepay_checkout_link(): void
    {
        Carbon::setTestNow('2026-06-18 09:00:00');

        config([
            'services.infinitepay.handle' => 'aura-handle',
            'services.infinitepay.base_url' => 'https://api.checkout.infinitepay.io',
            'aura.plans.start.monthly_price' => 549.00,
        ]);

        Http::fake([
            'api.checkout.infinitepay.io/links' => Http::response([
                'url' => 'https://checkout.infinitepay.com.br/aura?lenc=abc',
                'invoice_slug' => 'inv_abc_123',
            ], 200),
        ]);

        $company = Company::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'legal_name' => 'Copiadora X LTDA',
            'trade_name' => 'Copiadora X',
            'email' => 'financeiro@copiadorax.com',
            'plan' => 'start',
            'equipment_limit' => 200,
            'is_active' => true,
            'trial_starts_at' => Carbon::now()->subDays(23),
            'trial_ends_at' => Carbon::now()->addDays(7),
        ]);

        User::query()->create([
            'name' => 'Admin Trial',
            'email' => 'admin@copiadorax.com',
            'password' => 'password123',
            'company_id' => (int) $company->id,
            'tenant_id' => (int) $company->id,
            'role' => 'admin',
        ]);

        $stats = app(TrialLifecycleService::class)->processAllTrials();

        $company->refresh();

        $this->assertSame(1, $stats['notified']);
        $this->assertSame(1, $stats['links_created']);
        $this->assertSame('d7', $company->trial_last_notice_stage);
        $this->assertSame('payment_pending', $company->trial_status);
        $this->assertNotNull($company->infinitepay_order_nsu);
        $this->assertSame('https://checkout.infinitepay.com.br/aura?lenc=abc', $company->infinitepay_checkout_url);

        $this->assertDatabaseHas('system_notifications', [
            'tenant_id' => (int) $company->id,
            'channel' => 'email',
            'subject' => 'Trial Aura: faltam 7 dias para encerrar',
        ]);
    }

    public function test_it_does_not_send_duplicate_warning_for_same_stage(): void
    {
        Carbon::setTestNow('2026-06-18 09:00:00');

        config([
            'services.infinitepay.handle' => null,
        ]);

        $company = Company::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'legal_name' => 'Print Demo LTDA',
            'email' => 'contato@printdemo.com',
            'trial_starts_at' => Carbon::now()->subDays(23),
            'trial_ends_at' => Carbon::now()->addDays(7),
            'trial_last_notice_stage' => 'd7',
            'trial_last_notice_at' => Carbon::now()->subHours(2),
        ]);

        User::query()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@printdemo.com',
            'password' => 'password123',
            'company_id' => (int) $company->id,
            'tenant_id' => (int) $company->id,
            'role' => 'admin',
        ]);

        $stats = app(TrialLifecycleService::class)->processAllTrials();

        $this->assertSame(0, $stats['notified']);
        $this->assertDatabaseCount('system_notifications', 0);
    }

    public function test_infinitepay_webhook_marks_trial_as_paid(): void
    {
        Carbon::setTestNow('2026-06-24 10:00:00');

        $company = Company::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'legal_name' => 'Aura Trial LTDA',
            'email' => 'admin@auratrial.com',
            'trial_starts_at' => Carbon::now()->subDays(29),
            'trial_ends_at' => Carbon::now()->addDay(),
            'trial_status' => 'payment_pending',
        ]);

        $company->forceFill([
            'infinitepay_order_nsu' => 'aura-'.$company->id.'-start-20260624100000',
        ])->save();

        $response = $this->postJson('/api/v1/billing/infinitepay/webhook', [
            'order_nsu' => 'aura-'.$company->id.'-start-20260624100000',
            'invoice_slug' => 'inv_paid_123',
            'transaction_nsu' => 'txn_123',
            'capture_method' => 'pix',
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
        ]);

        $company->refresh();

        $this->assertSame('active', $company->trial_status);
        $this->assertNotNull($company->trial_converted_at);
        $this->assertSame('inv_paid_123', $company->infinitepay_checkout_slug);
    }
}
