<?php

namespace App\Filament\Resources\Contracts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContractInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resumo')
                    ->schema([
                        TextEntry::make('code')
                            ->label('Código'),
                        TextEntry::make('client.name')
                            ->label('Cliente'),
                        TextEntry::make('clientUnit.name')
                            ->label('Unidade')
                            ->placeholder('-'),
                        TextEntry::make('type')
                            ->label('Tipo'),
                        TextEntry::make('external_contract_number')
                            ->label('Nro contrato')
                            ->placeholder('-'),
                        TextEntry::make('status')
                            ->label('Status'),
                        TextEntry::make('payment_method')
                            ->label('Forma pagamento')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'cobranca_bancaria' => 'Cobrança bancária',
                                'pix' => 'PIX',
                                'debito_automatico' => 'Débito automático',
                                default => $state ?: '-',
                            }),
                        TextEntry::make('due_days')
                            ->label('Prazo (dias corridos)')
                            ->placeholder('-'),
                        TextEntry::make('monthly_fee')
                            ->label('Mensalidade')
                            ->money('BRL'),
                        TextEntry::make('start_date')
                            ->label('Início')
                            ->date('d/m/Y'),
                        TextEntry::make('end_date')
                            ->label('Fim')
                            ->date('d/m/Y')
                            ->placeholder('-'),
                        TextEntry::make('included_bw_pages')
                            ->label('Franquia PT (cópias)'),
                        TextEntry::make('included_color_pages')
                            ->label('Franquia Color (cópias)'),
                        TextEntry::make('bw_overage_price')
                            ->label('Excedente PT')
                            ->money('BRL'),
                        TextEntry::make('color_overage_price')
                            ->label('Excedente Color')
                            ->money('BRL'),
                    ])
                    ->columns(2),
            ]);
    }
}
