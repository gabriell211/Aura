<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resumo')
                    ->schema([
                        TextEntry::make('billing_reference')
                            ->label('Referência'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                        TextEntry::make('client.name')
                            ->label('Cliente'),
                        TextEntry::make('contract.code')
                            ->label('Contrato'),
                        TextEntry::make('period_start')
                            ->label('Período Inicial')
                            ->date('d/m/Y'),
                        TextEntry::make('period_end')
                            ->label('Período Final')
                            ->date('d/m/Y'),
                        TextEntry::make('due_date')
                            ->label('Vencimento')
                            ->date('d/m/Y'),
                        TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->money('BRL'),
                        TextEntry::make('excess_total')
                            ->label('Excedente')
                            ->money('BRL'),
                        TextEntry::make('total')
                            ->label('Total')
                            ->money('BRL'),
                        TextEntry::make('issued_at')
                            ->label('Emitida em')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('paid_at')
                            ->label('Paga em')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }
}
