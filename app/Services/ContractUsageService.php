<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\MeterRead;
use Carbon\CarbonImmutable;

class ContractUsageService
{
    public function summarizeForPeriod(Contract $contract, CarbonImmutable $periodStart, CarbonImmutable $periodEnd): array
    {
        $contract->loadMissing('equipments');

        $bwUsage = 0;
        $colorUsage = 0;

        foreach ($contract->equipments as $equipment) {
            $baselineRead = MeterRead::query()
                ->where('tenant_id', $contract->tenant_id)
                ->where('equipment_id', $equipment->id)
                ->where('read_at', '<', $periodStart)
                ->orderByDesc('read_at')
                ->first();

            $latestReadInPeriod = MeterRead::query()
                ->where('tenant_id', $contract->tenant_id)
                ->where('equipment_id', $equipment->id)
                ->whereBetween('read_at', [$periodStart, $periodEnd])
                ->orderByDesc('read_at')
                ->first();

            if ($latestReadInPeriod === null) {
                continue;
            }

            $monoBaseline = (int) ($baselineRead?->mono_total ?? 0);
            $colorBaseline = (int) ($baselineRead?->color_total ?? 0);

            $bwUsage += max(0, (int) $latestReadInPeriod->mono_total - $monoBaseline);
            $colorUsage += max(0, (int) $latestReadInPeriod->color_total - $colorBaseline);
        }

        $includedBw = (int) $contract->included_bw_pages;
        $includedColor = (int) $contract->included_color_pages;

        $bwOveragePages = max(0, $bwUsage - $includedBw);
        $colorOveragePages = max(0, $colorUsage - $includedColor);

        $bwOverageTotal = $bwOveragePages * (float) $contract->bw_overage_price;
        $colorOverageTotal = $colorOveragePages * (float) $contract->color_overage_price;

        $excessTotal = round($bwOverageTotal + $colorOverageTotal, 2);
        $thresholdMultiplier = (float) config('aura.billing.anomaly_threshold_multiplier', 1.30);

        $bwAnomaly = $includedBw > 0 && $bwUsage >= ($includedBw * $thresholdMultiplier);
        $colorAnomaly = $includedColor > 0 && $colorUsage >= ($includedColor * $thresholdMultiplier);

        return [
            'bw_usage_pages' => $bwUsage,
            'color_usage_pages' => $colorUsage,
            'bw_overage_pages' => $bwOveragePages,
            'color_overage_pages' => $colorOveragePages,
            'bw_overage_total' => round($bwOverageTotal, 2),
            'color_overage_total' => round($colorOverageTotal, 2),
            'excess_total' => $excessTotal,
            'anomaly_detected' => $bwAnomaly || $colorAnomaly,
        ];
    }
}
