<?php

namespace App\Filament\Resources\Technicians\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TechnicianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Tecnico')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(40),
                        Select::make('user_id')
                            ->label('Usuario vinculado')
                            ->options(function (): array {
                                $tenantId = (int) (auth()->user()?->tenant_id ?: auth()->user()?->company_id ?: 0);

                                if ($tenantId < 1) {
                                    return [];
                                }

                                return User::query()
                                    ->where(function ($query) use ($tenantId): void {
                                        $query
                                            ->where('tenant_id', $tenantId)
                                            ->orWhere('company_id', $tenantId);
                                    })
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->preload(),
                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
