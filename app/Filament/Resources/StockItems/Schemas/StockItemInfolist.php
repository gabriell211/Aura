<?php

namespace App\Filament\Resources\StockItems\Schemas;

use App\Models\StockItem;
use App\Models\StockMovement;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resumo do Item')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        TextEntry::make('sku')
                            ->label('SKU')
                            ->placeholder('-'),
                        TextEntry::make('name')
                            ->label('Nome'),
                        TextEntry::make('category')
                            ->label('Categoria')
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('lifecycle_stage')
                            ->label('Ciclo de vida')
                            ->formatStateUsing(fn (?string $state): string => StockItem::lifecycleStageLabel($state))
                            ->badge()
                            ->color(fn (?string $state): string => StockItem::lifecycleStageColor($state)),
                        TextEntry::make('current_stock')
                            ->label('Estoque atual'),
                        TextEntry::make('minimum_stock')
                            ->label('Estoque mínimo'),
                        TextEntry::make('last_movement_type')
                            ->label('Ultima movimentacao')
                            ->formatStateUsing(fn (?string $state): string => StockMovement::movementTypeLabel($state))
                            ->badge()
                            ->color(fn (?string $state): string => StockMovement::movementTypeColor($state))
                            ->placeholder('-'),
                        TextEntry::make('last_movement_at')
                            ->label('Data da ultima movimentacao')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('unit')
                            ->label('Unidade'),
                        TextEntry::make('storage_location')
                            ->label('Local')
                            ->placeholder('-'),
                        TextEntry::make('lifecycle_notes')
                            ->label('Observacoes do ciclo')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Historico recente')
                    ->schema([
                        RepeatableEntry::make('movements')
                            ->label('Movimentacoes')
                            ->placeholder('Sem movimentacoes registradas.')
                            ->getStateUsing(fn (StockItem $record): array => $record->movements()
                                ->limit(10)
                                ->get()
                                ->map(fn (StockMovement $movement): array => [
                                    'moved_at' => $movement->moved_at,
                                    'movement_type' => $movement->movement_type,
                                    'quantity' => $movement->quantity,
                                    'reason' => $movement->reason,
                                ])
                                ->all())
                            ->schema([
                                TextEntry::make('moved_at')
                                    ->label('Quando')
                                    ->dateTime('d/m/Y H:i'),
                                TextEntry::make('movement_type')
                                    ->label('Tipo')
                                    ->formatStateUsing(fn (?string $state): string => StockMovement::movementTypeLabel($state))
                                    ->badge()
                                    ->color(fn (?string $state): string => StockMovement::movementTypeColor($state)),
                                TextEntry::make('quantity')
                                    ->label('Quantidade')
                                    ->formatStateUsing(fn (mixed $state): string => (int) $state > 0 ? '+'.(int) $state : (string) (int) $state),
                                TextEntry::make('reason')
                                    ->label('Motivo')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }
}
