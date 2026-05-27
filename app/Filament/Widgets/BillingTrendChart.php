<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Carbon\CarbonImmutable;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\ChartWidget;

class BillingTrendChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 3,
    ];

    protected ?string $heading = 'Faturamento';

    protected ?string $description = 'Evolucao de faturamento no periodo selecionado.';

    protected ?string $maxHeight = '320px';

    protected ?string $pollingInterval = '60s';

    protected ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => false,
            ],
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
            ],
        ],
    ];

    public ?string $filter = '6m';

    protected function getData(): array
    {
        if (filled($this->pageFilters['startDate'] ?? null) || filled($this->pageFilters['endDate'] ?? null)) {
            return $this->buildDataForCustomRange();
        }

        return $this->buildDataForMonthWindow();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDataForMonthWindow(): array
    {
        $monthWindow = match ($this->filter) {
            '12m' => 12,
            default => 6,
        };

        $referenceFormat = (string) config('aura.billing.reference_format', 'Ym');
        $months = collect(range($monthWindow - 1, 0))
            ->map(fn (int $index) => now()->subMonths($index)->startOfMonth()->toImmutable());

        $references = $months
            ->mapWithKeys(fn (CarbonImmutable $month) => [$month->format($referenceFormat) => $month->format('m/Y')]);

        $totals = Invoice::query()
            ->whereIn('billing_reference', $references->keys()->all())
            ->selectRaw('billing_reference, SUM(total) as total')
            ->groupBy('billing_reference')
            ->pluck('total', 'billing_reference');

        $dataset = $references
            ->keys()
            ->map(fn (string $reference): float => (float) ($totals[$reference] ?? 0))
            ->values()
            ->all();

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento (R$)',
                    'data' => $dataset,
                    'fill' => true,
                    'tension' => 0.35,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $references->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDataForCustomRange(): array
    {
        $start = filled($this->pageFilters['startDate'] ?? null)
            ? CarbonImmutable::parse((string) $this->pageFilters['startDate'])->startOfDay()
            : now()->subDays(30)->startOfDay()->toImmutable();
        $end = filled($this->pageFilters['endDate'] ?? null)
            ? CarbonImmutable::parse((string) $this->pageFilters['endDate'])->endOfDay()
            : now()->endOfDay()->toImmutable();

        if ($end->lessThan($start)) {
            [$start, $end] = [$end->startOfDay(), $start->endOfDay()];
        }

        $labels = [];
        $dataset = [];

        if ($start->diffInDays($end) <= 60) {
            for ($day = $start; $day->lessThanOrEqualTo($end); $day = $day->addDay()) {
                $labels[] = $day->format('d/m');
                $dataset[] = (float) Invoice::query()
                    ->whereBetween('created_at', [$day, $day->endOfDay()])
                    ->sum('total');
            }
        } else {
            $cursor = $start->startOfMonth();
            $lastMonth = $end->startOfMonth();

            while ($cursor->lessThanOrEqualTo($lastMonth)) {
                $labels[] = $cursor->format('m/Y');
                $dataset[] = (float) Invoice::query()
                    ->whereBetween('created_at', [$cursor->startOfMonth(), $cursor->endOfMonth()])
                    ->sum('total');
                $cursor = $cursor->addMonth();
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento (R$)',
                    'data' => $dataset,
                    'fill' => true,
                    'tension' => 0.35,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * @return array<string, string> | null
     */
    protected function getFilters(): ?array
    {
        return [
            '6m' => 'Ultimos 6 meses',
            '12m' => 'Ultimos 12 meses',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
