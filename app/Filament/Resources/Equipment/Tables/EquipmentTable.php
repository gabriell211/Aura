<?php

namespace App\Filament\Resources\Equipment\Tables;

use App\Models\Equipment;
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

class EquipmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('serial_number')
                    ->label('Serial')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('asset_tag')
                    ->label('Patrimônio')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('manufacturer')
                    ->label('Fabricante')
                    ->searchable(),
                TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable(),
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable(),
                TextColumn::make('department')
                    ->label('Departamento')
                    ->searchable()
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
                TextColumn::make('acquisition_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (?string $state): string => Equipment::acquisitionTypeLabel($state))
                    ->badge()
                    ->color(fn (?string $state): string => $state === Equipment::ACQUISITION_RECONDITIONED ? 'warning' : 'success')
                    ->sortable(),
                TextColumn::make('is_backup')
                    ->label('Backup')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Sim' : 'Nao')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'warning' : 'gray')
                    ->sortable(),
                TextColumn::make('installed_at')
                    ->label('Instalado em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->sortable(),
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
                SelectFilter::make('acquisition_type')
                    ->label('Tipo')
                    ->options(Equipment::acquisitionTypeOptions()),
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
