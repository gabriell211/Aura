<?php

namespace App\Filament\Resources\StockItems\Tables;

use App\Models\StockItem;
use App\Models\StockMovement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]))
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Categoria')
                    ->badge()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('lifecycle_stage')
                    ->label('Ciclo de vida')
                    ->formatStateUsing(fn (?string $state): string => StockItem::lifecycleStageLabel($state))
                    ->badge()
                    ->color(fn (?string $state): string => StockItem::lifecycleStageColor($state))
                    ->sortable(),
                TextColumn::make('current_stock')
                    ->label('Atual')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('minimum_stock')
                    ->label('Mínimo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_status')
                    ->label('Status estoque')
                    ->state(fn ($record): string => $record->current_stock <= $record->minimum_stock ? 'Baixo' : 'Saudável')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Baixo' ? 'danger' : 'success'),
                TextColumn::make('storage_location')
                    ->label('Local')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('last_movement_type')
                    ->label('Ult. mov.')
                    ->formatStateUsing(fn (?string $state): string => StockMovement::movementTypeLabel($state))
                    ->badge()
                    ->color(fn (?string $state): string => StockMovement::movementTypeColor($state))
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('last_movement_at')
                    ->label('Ultima movimentacao')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoria')
                    ->options([
                        'toner' => 'Toner',
                        'cilindro' => 'Cilindro',
                        'fusor' => 'Fusor',
                        'peca' => 'Peça',
                        'outros' => 'Outros',
                    ]),
                SelectFilter::make('lifecycle_stage')
                    ->label('Ciclo de vida')
                    ->options(StockItem::lifecycleStageOptions()),
                Filter::make('low_stock')
                    ->label('Apenas baixo estoque')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('current_stock', '<=', 'minimum_stock')),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
