<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Support\PanelTabs;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('role')
                    ->label('Perfil')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'admin' ? 'Administrador' : 'Usuario')
                    ->color(fn (?string $state): string => $state === 'admin' ? 'warning' : 'info'),
                TextColumn::make('allowed_tabs')
                    ->label('Abas')
                    ->state(function (User $record): string {
                        if ($record->isAdmin()) {
                            return 'Todas as abas';
                        }

                        $tabs = PanelTabs::labelsFor($record->allowed_tabs);

                        return count($tabs) > 0 ? implode(', ', $tabs) : 'Nenhuma';
                    })
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Perfil')
                    ->options([
                        'admin' => 'Administrador',
                        'user' => 'Usuario',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
