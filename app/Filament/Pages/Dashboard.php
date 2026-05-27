<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesPageTabAccess;
use App\Filament\Widgets\BillingTrendChart;
use App\Filament\Widgets\DashboardOverview;
use App\Filament\Widgets\QuickActionsWidget;
use App\Support\PanelTabs;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    use AuthorizesPageTabAccess;
    use HasFiltersAction;

    protected static ?string $title = 'Painel de Controle';

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedHome;

    protected static function tabAccessKey(): string
    {
        return PanelTabs::DASHBOARD;
    }

    public function getColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 4,
        ];
    }

    public function getWidgets(): array
    {
        return [
            DashboardOverview::class,
            BillingTrendChart::class,
            QuickActionsWidget::class,
        ];
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->label('Filtros do Dashboard')
                ->schema([
                    DatePicker::make('startDate')
                        ->label('Data inicial'),
                    DatePicker::make('endDate')
                        ->label('Data final')
                        ->afterOrEqual('startDate'),
                ]),
        ];
    }
}
