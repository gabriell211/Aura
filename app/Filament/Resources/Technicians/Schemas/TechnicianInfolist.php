<?php

namespace App\Filament\Resources\Technicians\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TechnicianInfolist
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
                        TextEntry::make('phone')
                            ->label('Telefone')
                            ->placeholder('-'),
                        TextEntry::make('user.name')
                            ->label('Usuario vinculado')
                            ->placeholder('-'),
                        IconEntry::make('is_active')
                            ->label('Ativo')
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }
}
