<?php

namespace App\Filament\Resources\MeterReads\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MeterReadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['client', 'latestMeterRead']))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('serial_number')
                    ->label('Equipamento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('manufacturer')
                    ->label('Fabricante')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'online' => 'success',
                        'offline' => 'danger',
                        'maintenance' => 'warning',
                        'alert' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('latestMeterRead.read_at')
                    ->label('Última leitura')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Sem leitura')
                    ->sortable(),
                TextColumn::make('latestMeterRead.mono_total')
                    ->label('Total Mono')
                    ->numeric()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('latestMeterRead.color_total')
                    ->label('Total Color')
                    ->numeric()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('latestMeterRead.source')
                    ->label('Origem')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'webhook' => 'success',
                        'api' => 'info',
                        'manual' => 'warning',
                        default => 'gray',
                    })
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                        'maintenance' => 'Manutenção',
                        'alert' => 'Alerta',
                        'retired' => 'Aposentado',
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
