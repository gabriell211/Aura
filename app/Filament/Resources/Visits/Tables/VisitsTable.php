<?php

namespace App\Filament\Resources\Visits\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VisitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('ticket.id')
                    ->label('OS')
                    ->sortable(),
                TextColumn::make('ticket.title')
                    ->label('Chamado')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('technician.name')
                    ->label('Tecnico')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ticket.client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('route_status')
                    ->label('Status da rota')
                    ->state(fn ($record): string => filled($record->ended_at) ? 'Concluida' : (filled($record->started_at) ? 'Em andamento' : 'Pendente'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Concluida' => 'success',
                        'Em andamento' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('started_at')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('ended_at')
                    ->label('Fim')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('technician_id')
                    ->label('Tecnico')
                    ->relationship('technician', 'name')
                    ->searchable()
                    ->preload(),
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
