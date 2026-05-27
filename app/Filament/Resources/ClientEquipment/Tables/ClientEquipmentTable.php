<?php

namespace App\Filament\Resources\ClientEquipment\Tables;

use App\Models\Equipment;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class ClientEquipmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
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
                TextColumn::make('department')
                    ->label('Departamento')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('is_backup')
                    ->label('Backup')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Sim' : 'Nao')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'warning' : 'gray'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('is_backup')
                    ->label('Backup')
                    ->options([
                        '1' => 'Sim',
                        '0' => 'Nao',
                    ]),
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
            ->recordActions([
                Action::make('updateDepartmentAndBackup')
                    ->label('Departamento / Backup')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->fillForm(fn (Equipment $record): array => [
                        'department' => $record->department,
                        'is_backup' => (bool) $record->is_backup,
                    ])
                    ->schema([
                        TextInput::make('department')
                            ->label('Departamento')
                            ->maxLength(120),
                        Toggle::make('is_backup')
                            ->label('Equipamento em backup'),
                    ])
                    ->action(function (Equipment $record, array $data): void {
                        $record->update([
                            'department' => filled($data['department'] ?? null) ? trim((string) $data['department']) : null,
                            'is_backup' => (bool) ($data['is_backup'] ?? false),
                        ]);
                    }),
            ])
            ->toolbarActions([]);
    }
}
