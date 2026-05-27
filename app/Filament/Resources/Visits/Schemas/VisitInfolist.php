<?php

namespace App\Filament\Resources\Visits\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VisitInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resumo da Rota')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        TextEntry::make('ticket.id')
                            ->label('OS'),
                        TextEntry::make('ticket.title')
                            ->label('Chamado'),
                        TextEntry::make('technician.name')
                            ->label('Tecnico'),
                        TextEntry::make('equipment.serial_number')
                            ->label('Equipamento')
                            ->placeholder('-'),
                        TextEntry::make('ticket.client.name')
                            ->label('Cliente')
                            ->placeholder('-'),
                        TextEntry::make('started_at')
                            ->label('Inicio')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('ended_at')
                            ->label('Fim')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('signature_name')
                            ->label('Assinatura')
                            ->placeholder('-'),
                        TextEntry::make('notes')
                            ->label('Observacoes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
