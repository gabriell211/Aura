<?php

namespace App\Filament\Resources\StockItems\Schemas;

use App\Models\StockItem;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Suprimento')
                    ->schema([
                        TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(255),
                        TextInput::make('name')
                            ->label('Nome do item')
                            ->required()
                            ->maxLength(255),
                        Select::make('category')
                            ->label('Categoria')
                            ->options([
                                'toner' => 'Toner',
                                'cilindro' => 'Cilindro',
                                'fusor' => 'Fusor',
                                'peca' => 'Peça',
                                'outros' => 'Outros',
                            ]),
                        TextInput::make('unit')
                            ->label('Unidade')
                            ->default('un')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('minimum_stock')
                            ->label('Estoque mínimo')
                            ->integer()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        TextInput::make('current_stock')
                            ->label('Estoque atual')
                            ->integer()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        TextInput::make('storage_location')
                            ->label('Local de armazenamento')
                            ->maxLength(255),
                        Select::make('lifecycle_stage')
                            ->label('Ciclo de vida')
                            ->options(StockItem::lifecycleStageOptions())
                            ->required()
                            ->default(StockItem::LIFECYCLE_STAGE_IN_STOCK),
                        Textarea::make('lifecycle_notes')
                            ->label('Observacoes do ciclo')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Ex.: lote reservado para contrato X, aguardando instalacao.'),
                    ])
                    ->columns(2),
            ]);
    }
}
