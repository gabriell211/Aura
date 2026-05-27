<?php

namespace App\Filament\Resources\TechnicianOrders;

use App\Filament\Concerns\AuthorizesTabAccess;
use App\Filament\Resources\TechnicianOrders\Pages\ListTechnicianOrders;
use App\Filament\Resources\TechnicianOrders\Pages\ViewTechnicianOrder;
use App\Filament\Resources\TechnicianOrders\Tables\TechnicianOrdersTable;
use App\Filament\Resources\Tickets\Schemas\TicketInfolist;
use App\Models\Ticket;
use App\Models\User;
use App\Support\PanelTabs;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TechnicianOrderResource extends Resource
{
    use AuthorizesTabAccess;

    protected static ?string $model = Ticket::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Minhas OS';

    protected static ?string $modelLabel = 'OS';

    protected static ?string $pluralModelLabel = 'Minhas OS';

    protected static string | \UnitEnum | null $navigationGroup = 'SLA & Suporte';

    protected static ?int $navigationSort = 50;

    protected static ?string $slug = 'minhas-os';

    protected static function tabAccessKey(): string
    {
        return PanelTabs::TECHNICIAN_ORDERS;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TicketInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TechnicianOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTechnicianOrders::route('/'),
            'view' => ViewTechnicianOrder::route('/{record}'),
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

        return $query
            ->whereHas('visits.technician', fn (Builder $technicianQuery): Builder => $technicianQuery->where('user_id', $user->id))
            ->distinct();
    }
}
