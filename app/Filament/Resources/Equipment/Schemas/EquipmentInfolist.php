<?php

namespace App\Filament\Resources\Equipment\Schemas;

use App\Models\Equipment;
use App\Models\EquipmentPartUsage;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EquipmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resumo')
                    ->schema([
                        TextEntry::make('serial_number')
                            ->label('Serial'),
                        TextEntry::make('asset_tag')
                            ->label('Patrimônio')
                            ->placeholder('-'),
                        TextEntry::make('manufacturer')
                            ->label('Fabricante'),
                        TextEntry::make('model')
                            ->label('Modelo'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                        TextEntry::make('acquisition_type')
                            ->label('Tipo da maquina')
                            ->formatStateUsing(fn (?string $state): string => Equipment::acquisitionTypeLabel($state))
                            ->badge(),
                        TextEntry::make('client.name')
                            ->label('Cliente'),
                        TextEntry::make('contract.code')
                            ->label('Contrato')
                            ->placeholder('-'),
                        TextEntry::make('location')
                            ->label('Localização')
                            ->placeholder('-'),
                        TextEntry::make('department')
                            ->label('Departamento')
                            ->placeholder('-'),
                        TextEntry::make('is_backup')
                            ->label('Backup')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Sim' : 'Nao')
                            ->badge(),
                        TextEntry::make('installed_at')
                            ->label('Instalação')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                    ])
                    ->columns(2),
                Section::make('Pecas usadas')
                    ->schema([
                        RepeatableEntry::make('partUsages')
                            ->label('Itens consumidos')
                            ->placeholder('Sem pecas registradas para esta maquina.')
                            ->getStateUsing(fn (Equipment $record): array => $record->partUsages()
                                ->with('stockItem')
                                ->orderBy('id')
                                ->get()
                                ->map(fn (EquipmentPartUsage $usage): array => [
                                    'item' => $usage->stockItem?->name ?? 'Item removido',
                                    'quantity' => (int) $usage->quantity,
                                    'notes' => $usage->notes,
                                    'used_at' => $usage->used_at,
                                ])
                                ->all())
                            ->schema([
                                TextEntry::make('item')
                                    ->label('Peca'),
                                TextEntry::make('quantity')
                                    ->label('Qtd usada'),
                                TextEntry::make('used_at')
                                    ->label('Quando')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('notes')
                                    ->label('Observacoes')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }
}
