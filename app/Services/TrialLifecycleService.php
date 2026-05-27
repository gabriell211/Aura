<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Models\Company;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TrialLifecycleService
{
    public function __construct(
        private readonly InfinitePayCheckoutService $infinitePayCheckoutService,
    ) {
    }

    public function processAllTrials(): array
    {
        $stats = [
            'processed' => 0,
            'notified' => 0,
            'links_created' => 0,
            'errors' => 0,
        ];

        Company::query()
            ->whereNotNull('trial_ends_at')
            ->whereNull('trial_converted_at')
            ->where('trial_status', '!=', 'active')
            ->orderBy('id')
            ->chunkById(100, function ($companies) use (&$stats): void {
                foreach ($companies as $company) {
                    $stats['processed']++;

                    try {
                        $result = $this->processCompany($company);
                        $stats['notified'] += $result['notified'];
                        $stats['links_created'] += $result['link_created'] ? 1 : 0;
                    } catch (Throwable $exception) {
                        $stats['errors']++;

                        Log::error('Trial lifecycle processing failed.', [
                            'company_id' => (int) $company->id,
                            'message' => $exception->getMessage(),
                        ]);
                    }
                }
            });

        return $stats;
    }

    public function markCompanyAsPaid(Company $company, array $payload = []): void
    {
        $company->forceFill([
            'trial_status' => 'active',
            'trial_converted_at' => $company->trial_converted_at ?: now(),
            'trial_expired_at' => null,
            'is_active' => true,
            'infinitepay_checkout_slug' => (string) ($payload['invoice_slug'] ?? $company->infinitepay_checkout_slug),
            'updated_at' => now(),
        ])->save();
    }

    private function processCompany(Company $company): array
    {
        $stage = $this->resolveStage($company->trial_ends_at);

        if ($stage === null) {
            return ['notified' => 0, 'link_created' => false];
        }

        if (! $this->shouldNotifyStage($company, $stage)) {
            return ['notified' => 0, 'link_created' => false];
        }

        $linkCreated = false;

        if ($stage !== 'expired' && blank($company->infinitepay_checkout_url)) {
            if ($this->infinitePayCheckoutService->isConfigured()) {
                try {
                    $linkData = $this->infinitePayCheckoutService->createTrialUpgradeLink($company);

                    $company->forceFill([
                        'infinitepay_order_nsu' => (string) $linkData['order_nsu'],
                        'infinitepay_checkout_url' => (string) $linkData['checkout_url'],
                        'infinitepay_checkout_slug' => (string) ($linkData['invoice_slug'] ?? ''),
                        'infinitepay_checkout_generated_at' => now(),
                    ])->save();

                    $linkCreated = true;
                } catch (Throwable $exception) {
                    Log::warning('InfinitePay checkout generation failed, using fallback if available.', [
                        'company_id' => (int) $company->id,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }

            if (blank($company->infinitepay_checkout_url)) {
                $fallbackCheckoutUrl = $this->infinitePayCheckoutService->fallbackCheckoutUrl((string) ($company->plan ?: 'start'));

                if ($fallbackCheckoutUrl !== null) {
                    $company->forceFill([
                        'infinitepay_checkout_url' => $fallbackCheckoutUrl,
                        'infinitepay_checkout_generated_at' => now(),
                    ])->save();

                    $linkCreated = true;
                }
            }
        }

        if ($stage === 'expired' && $company->trial_expired_at === null) {
            $company->forceFill([
                'trial_status' => 'expired',
                'trial_expired_at' => now(),
            ])->save();
        } elseif ($company->trial_status === 'trialing') {
            $company->forceFill([
                'trial_status' => 'payment_pending',
            ])->save();
        }

        $notifications = $this->dispatchNotifications($company, $stage);

        $company->forceFill([
            'trial_last_notice_stage' => $stage,
            'trial_last_notice_at' => now(),
        ])->save();

        return ['notified' => $notifications, 'link_created' => $linkCreated];
    }

    private function dispatchNotifications(Company $company, string $stage): int
    {
        [$subject, $message] = $this->buildMessage($company, $stage);
        $adminUsers = User::query()
            ->where('company_id', (int) $company->id)
            ->where('role', 'admin')
            ->get(['id', 'email', 'name']);

        $created = 0;

        foreach ($adminUsers as $adminUser) {
            $notification = SystemNotification::query()->create([
                'tenant_id' => (int) $company->id,
                'user_id' => (int) $adminUser->id,
                'channel' => 'email',
                'subject' => $subject,
                'message' => $message,
                'status' => 'pending',
                'created_by' => null,
                'updated_by' => null,
            ]);

            SendNotificationJob::dispatch((int) $notification->id);
            $created++;

            $this->sendEmailSafely((string) $adminUser->email, $subject, $message);
        }

        if ($created === 0 && filled($company->email)) {
            $notification = SystemNotification::query()->create([
                'tenant_id' => (int) $company->id,
                'user_id' => null,
                'channel' => 'email',
                'subject' => $subject,
                'message' => $message,
                'status' => 'pending',
                'created_by' => null,
                'updated_by' => null,
            ]);

            SendNotificationJob::dispatch((int) $notification->id);
            $created++;

            $this->sendEmailSafely((string) $company->email, $subject, $message);
        }

        return $created;
    }

    private function sendEmailSafely(string $to, string $subject, string $message): void
    {
        try {
            Mail::raw($message, function ($mail) use ($to, $subject): void {
                $mail->to($to)->subject($subject);
            });
        } catch (Throwable $exception) {
            Log::warning('Trial reminder email send failed.', [
                'to' => $to,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function buildMessage(Company $company, string $stage): array
    {
        $trialEndsAt = $company->trial_ends_at instanceof Carbon
            ? $company->trial_ends_at->format('d/m/Y')
            : '';

        $companyName = (string) ($company->trade_name ?: $company->legal_name);
        $planName = strtoupper((string) ($company->plan ?: 'start'));
        $checkoutUrl = (string) ($company->infinitepay_checkout_url ?: '');

        $linkLine = $checkoutUrl !== ''
            ? PHP_EOL.'Link para ativar assinatura: '.$checkoutUrl
            : PHP_EOL.'Link de pagamento sera enviado assim que a configuracao da InfinitePay estiver ativa.';

        if ($stage === 'expired') {
            return [
                'Trial Aura expirado',
                "Empresa: {$companyName}".PHP_EOL.
                "Seu periodo de teste foi encerrado em {$trialEndsAt}.".PHP_EOL.
                "Voce ainda pode ativar a assinatura para continuar no Aura.".
                $linkLine,
            ];
        }

        $daysToken = (int) ltrim($stage, 'd');

        if ($daysToken <= 1) {
            return [
                'Trial Aura: ultimo dia para ativar assinatura',
                "Empresa: {$companyName}".PHP_EOL.
                "Seu trial encerra em {$trialEndsAt}.".PHP_EOL.
                "Hoje e o ultimo dia para ativar a assinatura do plano {$planName}.".
                $linkLine,
            ];
        }

        return [
            sprintf('Trial Aura: faltam %d dias para encerrar', $daysToken),
            "Empresa: {$companyName}".PHP_EOL.
            "Seu trial encerra em {$trialEndsAt}.".PHP_EOL.
            "Para manter seu ambiente ativo, conclua a assinatura do plano {$planName}.".
            $linkLine,
        ];
    }

    private function resolveStage(?Carbon $trialEndsAt): ?string
    {
        if ($trialEndsAt === null) {
            return null;
        }

        $daysRemaining = now()->startOfDay()->diffInDays($trialEndsAt->copy()->startOfDay(), false);

        if ($daysRemaining < 0) {
            return 'expired';
        }

        /** @var Collection<int, int> $warningDays */
        $warningDays = collect((array) config('aura.trial.warning_days', [7, 3, 1]))
            ->map(fn (mixed $value): int => (int) $value)
            ->filter(fn (int $value): bool => $value >= 0)
            ->unique()
            ->sort()
            ->values();

        foreach ($warningDays as $warningDay) {
            if ($daysRemaining <= $warningDay) {
                return 'd'.$warningDay;
            }
        }

        return null;
    }

    private function shouldNotifyStage(Company $company, string $stage): bool
    {
        return (string) $company->trial_last_notice_stage !== $stage;
    }
}
