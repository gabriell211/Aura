<?php

namespace App\Filament\Resources\ClientEquipment;

use App\Filament\Concerns\AuthorizesTabAccess;
use App\Filament\Resources\ClientEquipment\Pages\ListClientEquipment;
use App\Filament\Resources\ClientEquipment\Tables\ClientEquipmentTable;
use App\Models\Equipment;
use App\Models\User;
use App\Support\PanelTabs;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientEquipmentResource extends Resource
{
    use AuthorizesTabAccess;

    protected static ?string $model = Equipment::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Equipamentos Cliente';

    protected static ?string $modelLabel = 'Equipamento Cliente';

    protected static ?string $pluralModelLabel = 'Equipamentos Cliente';

    protected static string | \UnitEnum | null $navigationGroup = 'Operacional';

    protected static ?int $navigationSort = 33;

    protected static ?string $slug = 'equipamentos-cliente';

    protected static function tabAccessKey(): string
    {
        return PanelTabs::CLIENT_EQUIPMENT;
    }

    public static function table(Table $table): Table
    {
        return ClientEquipmentTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientEquipment::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var User|null $user */
        $user = auth()->user();

        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        if ((int) ($user->client_id ?? 0) < 1) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('client_id', (int) $user->client_id);
    }
}
