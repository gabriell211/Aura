<?php

namespace App\Http\Controllers;

class ErpBlueprintController extends Controller
{
    public function __invoke()
    {
        $plans = collect((array) config('aura.plans', []))
            ->map(function (array $plan, string $key): array {
                $price = $plan['monthly_price'] ?? null;

                return [
                    'key' => $key,
                    'equipment_limit' => $plan['equipment_limit'] ?? null,
                    'monthly_price' => is_numeric($price) ? (float) $price : null,
                    'monthly_price_label' => is_numeric($price)
                        ? 'R$ '.number_format((float) $price, 2, ',', '.').'/mes'
                        : 'Sob consulta',
                ];
            })
            ->all();

        return view('erp.blueprint', [
            'plans' => $plans,
        ]);
    }
}
