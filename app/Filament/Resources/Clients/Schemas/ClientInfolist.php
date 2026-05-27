<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resumo')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        TextEntry::make('name')
                            ->label('Nome'),
                        TextEntry::make('document')
                            ->label('Documento')
                            ->placeholder('-'),
                        TextEntry::make('email')
                            ->label('E-mail')
                            ->placeholder('-'),
                        TextEntry::make('phone')
                            ->label('Telefone')
                            ->placeholder('-'),
                        TextEntry::make('billing_contact')
                            ->label('Contato de Faturamento')
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }
}
