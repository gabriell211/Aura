<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resumo')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        TextEntry::make('title')
                            ->label('Título'),
                        TextEntry::make('client.name')
                            ->label('Cliente'),
                        TextEntry::make('equipment.serial_number')
                            ->label('Equipamento')
                            ->placeholder('-'),
                        TextEntry::make('priority')
                            ->label('Prioridade')
                            ->badge(),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                        TextEntry::make('origin')
                            ->label('Origem'),
                        TextEntry::make('external_source')
                            ->label('Fonte Externa')
                            ->placeholder('-'),
                        TextEntry::make('external_reference')
                            ->label('Referência Externa')
                            ->placeholder('-'),
                        TextEntry::make('opened_at')
                            ->label('Aberto em')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('closed_at')
                            ->label('Fechado em')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('description')
                            ->label('Descrição')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
