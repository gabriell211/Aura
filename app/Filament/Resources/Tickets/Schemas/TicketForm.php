<?php

namespace App\Filament\Resources\Tickets\Schemas;

use App\Enums\TicketStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Chamado')
                    ->schema([
                        Select::make('client_id')
                            ->label('Cliente')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('equipment_id')
                            ->label('Equipamento')
                            ->relationship('equipment', 'serial_number')
                            ->searchable()
                            ->preload(),
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Descrição')
                            ->rows(4)
                            ->columnSpanFull(),
                        Select::make('priority')
                            ->label('Prioridade')
                            ->options([
                                'low' => 'Baixa',
                                'medium' => 'Média',
                                'high' => 'Alta',
                                'critical' => 'Crítica',
                            ])
                            ->required()
                            ->default('medium'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                TicketStatus::OPEN->value => 'Aberto',
                                TicketStatus::TRIAGE->value => 'Triagem',
                                TicketStatus::DISPATCHED->value => 'Despachado',
                                TicketStatus::IN_PROGRESS->value => 'Em andamento',
                                TicketStatus::RESOLVED->value => 'Resolvido',
                                TicketStatus::CLOSED->value => 'Fechado',
                            ])
                            ->required()
                            ->default(TicketStatus::OPEN->value),
                        Select::make('origin')
                            ->label('Origem')
                            ->options([
                                'manual' => 'Manual',
                                'monitoring' => 'Monitoramento',
                                'customer_portal' => 'Portal do Cliente',
                                'billing' => 'Faturamento',
                            ])
                            ->required()
                            ->default('manual'),
                        DateTimePicker::make('opened_at')
                            ->label('Aberto em')
                            ->seconds(false)
                            ->default(fn () => now()),
                        DateTimePicker::make('closed_at')
                            ->label('Fechado em')
                            ->seconds(false),
                    ])
                    ->columns(2),
            ]);
    }
}
