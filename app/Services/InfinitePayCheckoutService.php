<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class InfinitePayCheckoutService
{
    public function isConfigured(): bool
    {
        return filled($this->resolveHandle());
    }

    public function createTrialUpgradeLink(Company $company, ?string $planKey = null): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('InfinitePay handle is not configured.');
        }

        $planKey ??= (string) ($company->plan ?: 'start');
        $planConfig = (array) config("aura.plans.{$planKey}", []);
        $monthlyPrice = $planConfig['monthly_price'] ?? null;

        if (! is_numeric($monthlyPrice) || (float) $monthlyPrice <= 0) {
            throw new RuntimeException('Plan monthly price is missing for checkout generation.');
        }

        $amountCents = (int) round(((float) $monthlyPrice) * 100);
        $orderNsu = $this->buildOrderNsu($company, $planKey);
        $description = sprintf(
            'Aura ERP MPS - Plano %s mensal',
            Str::upper($planKey)
        );

        $payload = $this->buildCheckoutPayload(
            company: $company,
            amountCents: $amountCents,
            orderNsu: $orderNsu,
            description: $description,
        );

        $response = $this->client()->post('/links', $payload)->throw()->json();
        $checkoutUrl = (string) data_get($response, 'url', '');

        if ($checkoutUrl === '') {
            throw new RuntimeException('InfinitePay did not return a checkout URL.');
        }

        return [
            'order_nsu' => $orderNsu,
            'checkout_url' => $checkoutUrl,
            'invoice_slug' => data_get($response, 'invoice_slug'),
            'amount_cents' => $amountCents,
            'payload' => $payload,
            'response' => $response,
        ];
    }

    public function checkPaymentStatus(string $orderNsu, string $transactionNsu, string $slug): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('InfinitePay handle is not configured.');
        }

        return $this->client()
            ->post('/payment_check', [
                'handle' => $this->resolveHandle(),
                'order_nsu' => $orderNsu,
                'transaction_nsu' => $transactionNsu,
                'slug' => $slug,
            ])
            ->throw()
            ->json();
    }

    public function fallbackCheckoutUrl(?string $planKey = null): ?string
    {
        if ($planKey !== null) {
            $planUrl = trim((string) config('services.infinitepay.checkout_urls.'.$planKey, ''));

            if ($planUrl !== '') {
                return $planUrl;
            }
        }

        $url = trim((string) config('services.infinitepay.fallback_checkout_url', ''));

        return $url !== '' ? $url : null;
    }

    private function buildCheckoutPayload(Company $company, int $amountCents, string $orderNsu, string $description): array
    {
        $payload = [
            'handle' => $this->resolveHandle(),
            'order_nsu' => $orderNsu,
            'items' => [[
                'quantity' => 1,
                'price' => $amountCents,
                'description' => $description,
            ]],
        ];

        $redirectUrl = $this->resolveRedirectUrl();
        $webhookUrl = $this->resolveWebhookUrl();

        if (filled($redirectUrl)) {
            $payload['redirect_url'] = $redirectUrl;
        }

        if (filled($webhookUrl)) {
            $payload['webhook_url'] = $webhookUrl;
        }

        $customerPhone = $this->normalizePhoneNumber((string) ($company->phone ?? ''));

        if (filled($company->trade_name ?: $company->legal_name) && filled($company->email) && $customerPhone !== null) {
            $payload['customer'] = [
                'name' => (string) ($company->trade_name ?: $company->legal_name),
                'email' => (string) $company->email,
                'phone_number' => $customerPhone,
            ];
        }

        return $payload;
    }

    private function buildOrderNsu(Company $company, string $planKey): string
    {
        return sprintf(
            'aura-%d-%s-%s',
            (int) $company->id,
            Str::lower($planKey),
            now()->format('YmdHis')
        );
    }

    private function resolveRedirectUrl(): ?string
    {
        $configured = trim((string) config('services.infinitepay.redirect_url', ''));

        if ($configured !== '') {
            return $configured;
        }

        return route('trial.create');
    }

    private function resolveWebhookUrl(): ?string
    {
        $configured = trim((string) config('services.infinitepay.webhook_url', ''));

        if ($configured !== '') {
            return $configured;
        }

        return route('api.v1.billing.infinitepay.webhook');
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->baseUrl(rtrim((string) config('services.infinitepay.base_url', 'https://api.checkout.infinitepay.io'), '/'))
            ->timeout((int) config('services.infinitepay.timeout', 15));
    }

    private function normalizePhoneNumber(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        if (! str_starts_with($digits, '55')) {
            $digits = '55'.$digits;
        }

        return '+'.$digits;
    }

    private function resolveHandle(): string
    {
        return ltrim(trim((string) config('services.infinitepay.handle', '')), '$');
    }
}
