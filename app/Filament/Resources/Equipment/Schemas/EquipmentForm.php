<?php

namespace App\Filament\Resources\Equipment\Schemas;

use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use App\Models\StockItem;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class EquipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Equipamento')
                    ->schema([
                        Select::make('client_id')
                            ->label('Cliente')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('client_unit_id')
                            ->label('Unidade')
                            ->relationship('clientUnit', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('contract_id')
                            ->label('Contrato')
                            ->relationship('contract', 'code')
                            ->searchable()
                            ->preload(),
                        TextInput::make('manufacturer')
                            ->label('Fabricante')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('model')
                            ->label('Modelo')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('serial_number')
                            ->label('Serial')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('asset_tag')
                            ->label('Patrimônio')
                            ->maxLength(120),
                        Select::make('acquisition_type')
                            ->label('Tipo da maquina')
                            ->options(Equipment::acquisitionTypeOptions())
                            ->default(Equipment::ACQUISITION_NEW)
                            ->required()
                            ->live(),
                        TextInput::make('ip_address')
                            ->label('IP')
                            ->ip()
                            ->maxLength(45),
                        TextInput::make('mac_address')
                            ->label('MAC')
                            ->maxLength(40),
                        TextInput::make('location')
                            ->label('Localização')
                            ->maxLength(255),
                        TextInput::make('department')
                            ->label('Departamento')
                            ->maxLength(120),
                        Toggle::make('is_backup')
                            ->label('Equipamento em backup')
                            ->default(false),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                EquipmentStatus::ONLINE->value => 'Online',
                                EquipmentStatus::OFFLINE->value => 'Offline',
                                EquipmentStatus::MAINTENANCE->value => 'Manutenção',
                                EquipmentStatus::ALERT->value => 'Alerta',
                                EquipmentStatus::RETIRED->value => 'Aposentado',
                            ])
                            ->required()
                            ->default(EquipmentStatus::ONLINE->value),
                        DateTimePicker::make('installed_at')
                            ->label('Instalado em')
                            ->seconds(false),
                        Repeater::make('part_usages')
                            ->label('Pecas utilizadas no recondicionamento')
                            ->schema([
                                Select::make('stock_item_id')
                                    ->label('Peca de estoque')
                                    ->options(fn (): array => StockItem::query()
                                        ->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(fn (StockItem $item): array => [
                                            (string) $item->id => "{$item->name} ({$item->current_stock} em estoque)",
                                        ])
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('quantity')
                                    ->label('Quantidade usada')
                                    ->integer()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required(),
                                Textarea::make('notes')
                                    ->label('Observacoes')
                                    ->rows(2)
                                    ->maxLength(500),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Adicionar peca utilizada')
                            ->required(fn (Get $get): bool => ($get('acquisition_type') ?? Equipment::ACQUISITION_NEW) === Equipment::ACQUISITION_RECONDITIONED)
                            ->minItems(1)
                            ->visible(fn (Get $get): bool => ($get('acquisition_type') ?? Equipment::ACQUISITION_NEW) === Equipment::ACQUISITION_RECONDITIONED)
                            ->helperText('Informe as pecas usadas para montar ou recondicionar esta maquina.'),
                    ])
                    ->columns(2),
            ]);
    }
}
