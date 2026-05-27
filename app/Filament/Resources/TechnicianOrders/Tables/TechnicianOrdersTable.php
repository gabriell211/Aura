<?php

namespace App\Filament\Resources\TechnicianOrders\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TechnicianOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('opened_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('OS')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Chamado')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('equipment.serial_number')
                    ->label('Equipamento')
                    ->placeholder('-'),
                TextColumn::make('priority')
                    ->label('Prioridade')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'critical' => 'danger',
                        'high' => 'warning',
                        'medium' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('opened_at')
                    ->label('Abertura')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Aberto',
                        'triage' => 'Triagem',
                        'dispatched' => 'Despachado',
                        'in_progress' => 'Em andamento',
                        'resolved' => 'Resolvido',
                        'closed' => 'Fechado',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
