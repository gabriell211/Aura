<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Equipment;
use App\Models\Invoice;
use App\Models\Ticket;
use Carbon\CarbonImmutable;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class DashboardOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Resumo Operacional';

    protected ?string $description = 'Visao geral da operacao do cliente.';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $dateRange = $this->resolveDateRange();

        $clientsTotal = Client::query()->count();
        $contractsActive = Contract::query()->where('status', 'active')->count();
        $equipmentTotal = Equipment::query()->count();
        $criticalEquipment = Equipment::query()->whereIn('status', ['offline', 'alert'])->count();
        $openTickets = Ticket::query()->whereNotIn('status', ['closed', 'resolved'])->count();

        $currentMonth = now()->format((string) config('aura.billing.reference_format', 'Ym'));
        $currentMonthBilling = (float) Invoice::query()
            ->where('billing_reference', $currentMonth)
            ->sum('total');

        return [
            Stat::make('Clientes', number_format($clientsTotal, 0, ',', '.'))
                ->description('Base ativa no tenant')
                ->descriptionIcon('heroicon-o-users')
                ->chart($this->countByDaySeries(Client::query(), 'created_at', 7))
                ->color('primary'),
            Stat::make('Contratos Ativos', number_format($contractsActive, 0, ',', '.'))
                ->description('Status: active')
                ->descriptionIcon('heroicon-o-document-text')
                ->chart($this->countByDaySeries(Contract::query()->where('status', 'active'), 'created_at', 7))
                ->color('success'),
            Stat::make('Equipamentos', number_format($equipmentTotal, 0, ',', '.'))
                ->description("Criticos: {$criticalEquipment}")
                ->descriptionIcon('heroicon-o-printer')
                ->chart($this->countByDaySeries(Equipment::query(), 'created_at', 7))
                ->color($criticalEquipment > 0 ? 'warning' : 'success'),
            Stat::make('Chamados Abertos', number_format($openTickets, 0, ',', '.'))
                ->description('Inclui open, triage, dispatched e in_progress')
                ->descriptionIcon('heroicon-o-wrench-screwdriver')
                ->chart($this->countByDaySeries(Ticket::query()->whereNotIn('status', ['closed', 'resolved']), 'created_at', 7))
                ->color($openTickets > 0 ? 'warning' : 'success'),
            Stat::make('Faturamento do Mes', 'R$ '.number_format($currentMonthBilling, 2, ',', '.'))
                ->description('Referencia '.$currentMonth)
                ->descriptionIcon('heroicon-o-banknotes')
                ->chart($this->billingByDaySeries(7))
                ->color('info'),
            Stat::make(
                'Novos Registros no Periodo',
                number_format($this->countCreatedBetween($dateRange['start'], $dateRange['end']), 0, ',', '.')
            )
                ->description($dateRange['label'])
                ->descriptionIcon('heroicon-o-calendar-days')
                ->chart($this->aggregateCreatedSeries(7))
                ->color('gray'),
        ];
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable, label: string}
     */
    private function resolveDateRange(): array
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

        return [
            'start' => $start,
            'end' => $end,
            'label' => sprintf('De %s ate %s', $start->format('d/m/Y'), $end->format('d/m/Y')),
        ];
    }

    private function countCreatedBetween(CarbonImmutable $start, CarbonImmutable $end): int
    {
        return Client::query()->whereBetween('created_at', [$start, $end])->count()
            + Contract::query()->whereBetween('created_at', [$start, $end])->count()
            + Equipment::query()->whereBetween('created_at', [$start, $end])->count()
            + Ticket::query()->whereBetween('created_at', [$start, $end])->count();
    }

    /**
     * @return array<int>
     */
    private function countByDaySeries(Builder $query, string $column, int $days): array
    {
        $today = now()->startOfDay()->toImmutable();
        $series = [];

        for ($index = $days - 1; $index >= 0; $index--) {
            $day = $today->subDays($index);
            $series[] = (clone $query)
                ->whereBetween($column, [$day, $day->endOfDay()])
                ->count();
        }

        return $series;
    }

    /**
     * @return array<float>
     */
    private function billingByDaySeries(int $days): array
    {
        $today = now()->startOfDay()->toImmutable();
        $series = [];

        for ($index = $days - 1; $index >= 0; $index--) {
            $day = $today->subDays($index);
            $series[] = (float) Invoice::query()
                ->whereBetween('created_at', [$day, $day->endOfDay()])
                ->sum('total');
        }

        return $series;
    }

    /**
     * @return array<int>
     */
    private function aggregateCreatedSeries(int $days): array
    {
        $today = now()->startOfDay()->toImmutable();
        $series = [];

        for ($index = $days - 1; $index >= 0; $index--) {
            $day = $today->subDays($index);
            $window = [$day, $day->endOfDay()];

            $series[] =
                Client::query()->whereBetween('created_at', $window)->count()
                + Contract::query()->whereBetween('created_at', $window)->count()
                + Equipment::query()->whereBetween('created_at', $window)->count()
                + Ticket::query()->whereBetween('created_at', $window)->count();
        }

        return $series;
    }
}
