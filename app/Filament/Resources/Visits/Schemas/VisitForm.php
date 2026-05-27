<?php

namespace App\Filament\Resources\Visits\Schemas;

use App\Models\Equipment;
use App\Models\Technician;
use App\Models\Ticket;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VisitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Planejamento da Rota')
                    ->schema([
                        Select::make('ticket_id')
                            ->label('OS / Chamado')
                            ->relationship('ticket', 'title')
                            ->getOptionLabelFromRecordUsing(fn (Ticket $record): string => "#{$record->id} - {$record->title}")
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('technician_id')
                            ->label('Tecnico')
                            ->relationship('technician', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Technician $record): string => $record->name)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('equipment_id')
                            ->label('Equipamento')
                            ->relationship('equipment', 'serial_number')
                            ->getOptionLabelFromRecordUsing(fn (Equipment $record): string => "{$record->serial_number} - {$record->manufacturer} {$record->model}")
                            ->searchable()
                            ->preload(),
                        DateTimePicker::make('started_at')
                            ->label('Inicio previsto/real')
                            ->seconds(false),
                        DateTimePicker::make('ended_at')
                            ->label('Fim previsto/real')
                            ->seconds(false),
                        TextInput::make('signature_name')
                            ->label('Assinatura')
                            ->maxLength(255),
                        Textarea::make('notes')
                            ->label('Observacoes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
