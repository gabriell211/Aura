<?php

namespace App\Filament\Resources\StockItems\Pages;

use App\Filament\Resources\StockItems\StockItemResource;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Services\StockLifecycleService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewStockItem extends ViewRecord
{
    protected static string $resource = StockItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('registerMovement')
                ->label('Movimentar estoque')
                ->icon('heroicon-o-arrows-right-left')
                ->color('info')
                ->schema([
                    Select::make('movement_type')
                        ->label('Tipo de movimentacao')
                        ->options(StockMovement::movementTypeOptions())
                        ->required(),
                    TextInput::make('quantity')
                        ->label('Quantidade')
                        ->integer()
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->required(),
                    DateTimePicker::make('moved_at')
                        ->label('Data da movimentacao')
                        ->seconds(false)
                        ->default(now()),
                    Select::make('lifecycle_stage')
                        ->label('Atualizar ciclo de vida (opcional)')
                        ->options(StockItem::lifecycleStageOptions())
                        ->placeholder('Manter ciclo atual'),
                    Textarea::make('reason')
                        ->label('Motivo')
                        ->rows(3)
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    app(StockLifecycleService::class)->registerMovement(
                        stockItem: $this->getRecord(),
                        movementType: (string) $data['movement_type'],
                        quantity: (int) $data['quantity'],
                        reason: $data['reason'] ?? null,
                        movedAt: $data['moved_at'] ?? null,
                        lifecycleStage: $data['lifecycle_stage'] ?? null,
                    );

                    $this->record = $this->getRecord()->fresh();

                    Notification::make()
                        ->success()
                        ->title('Movimentacao registrada com sucesso.')
                        ->send();
                }),
            Action::make('updateLifecycleStage')
                ->label('Atualizar ciclo de vida')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('warning')
                ->schema([
                    Select::make('lifecycle_stage')
                        ->label('Ciclo de vida')
                        ->options(StockItem::lifecycleStageOptions())
                        ->default(fn (): ?string => $this->getRecord()->lifecycle_stage)
                        ->required(),
                    Textarea::make('lifecycle_notes')
                        ->label('Observacoes')
                        ->rows(3)
                        ->default(fn (): ?string => $this->getRecord()->lifecycle_notes),
                ])
                ->action(function (array $data): void {
                    app(StockLifecycleService::class)->updateLifecycleStage(
                        stockItem: $this->getRecord(),
                        stage: (string) $data['lifecycle_stage'],
                        notes: $data['lifecycle_notes'] ?? null,
                    );

                    $this->record = $this->getRecord()->fresh();

                    Notification::make()
                        ->success()
                        ->title('Ciclo de vida atualizado.')
                        ->send();
                }),
            EditAction::make(),
        ];
    }
}
