<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Enums\InvoiceStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Fatura')
                    ->schema([
                        Select::make('client_id')
                            ->label('Cliente')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('contract_id')
                            ->label('Contrato')
                            ->relationship('contract', 'code')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('billing_reference')
                            ->label('Referência (AAAAMM)')
                            ->required()
                            ->maxLength(20),
                        DatePicker::make('period_start')
                            ->label('Período Inicial')
                            ->required(),
                        DatePicker::make('period_end')
                            ->label('Período Final')
                            ->required(),
                        DatePicker::make('due_date')
                            ->label('Vencimento')
                            ->required(),
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->required()
                            ->default(0),
                        TextInput::make('excess_total')
                            ->label('Excedente')
                            ->numeric()
                            ->required()
                            ->default(0),
                        TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->required()
                            ->default(0),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                InvoiceStatus::DRAFT->value => 'Rascunho',
                                InvoiceStatus::ISSUED->value => 'Emitida',
                                InvoiceStatus::PAID->value => 'Paga',
                                InvoiceStatus::OVERDUE->value => 'Vencida',
                                InvoiceStatus::CANCELED->value => 'Cancelada',
                            ])
                            ->required()
                            ->default(InvoiceStatus::DRAFT->value),
                        DateTimePicker::make('issued_at')
                            ->label('Emitida em')
                            ->seconds(false),
                        DateTimePicker::make('paid_at')
                            ->label('Paga em')
                            ->seconds(false),
                    ])
                    ->columns(2),
            ]);
    }
}
