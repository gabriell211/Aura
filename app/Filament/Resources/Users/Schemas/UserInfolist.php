<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Support\PanelTabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
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
                        TextEntry::make('email')
                            ->label('E-mail'),
                        TextEntry::make('client.name')
                            ->label('Cliente vinculado')
                            ->placeholder('-'),
                        TextEntry::make('role')
                            ->label('Perfil')
                            ->state(fn (User $record): string => $record->isAdmin() ? 'Administrador' : 'Usuario'),
                        TextEntry::make('allowed_tabs')
                            ->label('Abas permitidas')
                            ->state(function (User $record): string {
                                if ($record->isAdmin()) {
                                    return 'Todas as abas';
                                }

                                $tabs = PanelTabs::labelsFor($record->allowed_tabs);

                                return count($tabs) > 0 ? implode(', ', $tabs) : 'Nenhuma';
                            }),
                        TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }
}
