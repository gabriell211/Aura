<?php

namespace App\Filament\Resources\Visits;

use App\Filament\Concerns\AuthorizesTabAccess;
use App\Filament\Resources\Visits\Pages\CreateVisit;
use App\Filament\Resources\Visits\Pages\EditVisit;
use App\Filament\Resources\Visits\Pages\ListVisits;
use App\Filament\Resources\Visits\Pages\ViewVisit;
use App\Filament\Resources\Visits\Schemas\VisitForm;
use App\Filament\Resources\Visits\Schemas\VisitInfolist;
use App\Filament\Resources\Visits\Tables\VisitsTable;
use App\Models\Visit;
use App\Support\PanelTabs;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VisitResource extends Resource
{
    use AuthorizesTabAccess;

    protected static ?string $model = Visit::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Rotas';

    protected static ?string $modelLabel = 'Rota';

    protected static ?string $pluralModelLabel = 'Rotas';

    protected static string | \UnitEnum | null $navigationGroup = 'SLA & Suporte';

    protected static ?int $navigationSort = 15;

    protected static ?string $slug = 'rotas';

    protected static function tabAccessKey(): string
    {
        return PanelTabs::ROUTES;
    }

    public static function form(Schema $schema): Schema
    {
        return VisitForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VisitInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisits::route('/'),
            'create' => CreateVisit::route('/create'),
            'view' => ViewVisit::route('/{record}'),
            'edit' => EditVisit::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
