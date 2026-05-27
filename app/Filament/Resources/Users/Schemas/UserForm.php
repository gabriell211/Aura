<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Users\UserResource;
use App\Models\Client;
use App\Support\PanelTabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Usuario')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('role')
                            ->label('Perfil')
                            ->options(fn (): array => UserResource::canAssignAdminRole()
                                ? [
                                    'admin' => 'Administrador',
                                    'user' => 'Usuario',
                                ]
                                : [
                                    'user' => 'Usuario',
                                ])
                            ->default('user')
                            ->required()
                            ->live(),
                        Select::make('client_id')
                            ->label('Cliente vinculado (portal)')
                            ->options(fn (): array => Client::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => ($get('role') ?? 'user') !== 'admin')
                            ->helperText('Opcional. Se informado, o usuario ve somente os equipamentos deste cliente.'),
                        Select::make('allowed_tabs')
                            ->label('Abas permitidas')
                            ->options(PanelTabs::options())
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull()
                            ->required(fn (Get $get): bool => ($get('role') ?? 'user') !== 'admin')
                            ->visible(fn (Get $get): bool => ($get('role') ?? 'user') !== 'admin')
                            ->helperText('Escolha quais abas este usuario podera acessar no painel.'),
                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->maxLength(255)
                            ->minLength(8)
                            ->confirmed()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state)),
                        TextInput::make('password_confirmation')
                            ->label('Confirmar senha')
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false),
                    ])
                    ->columns(2),
            ]);
    }
}
