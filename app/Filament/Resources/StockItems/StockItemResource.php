<?php

namespace App\Filament\Resources\StockItems;

use App\Filament\Concerns\AuthorizesTabAccess;
use App\Filament\Resources\StockItems\Pages\CreateStockItem;
use App\Filament\Resources\StockItems\Pages\EditStockItem;
use App\Filament\Resources\StockItems\Pages\ListStockItems;
use App\Filament\Resources\StockItems\Pages\ViewStockItem;
use App\Filament\Resources\StockItems\Schemas\StockItemForm;
use App\Filament\Resources\StockItems\Schemas\StockItemInfolist;
use App\Filament\Resources\StockItems\Tables\StockItemsTable;
use App\Models\StockItem;
use App\Support\PanelTabs;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockItemResource extends Resource
{
    use AuthorizesTabAccess;

    protected static ?string $model = StockItem::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Suprimentos';

    protected static string | \UnitEnum | null $navigationGroup = 'Estoque';

    protected static ?int $navigationSort = 45;

    protected static ?string $slug = 'suprimentos';

    protected static function tabAccessKey(): string
    {
        return PanelTabs::STOCK_ITEMS;
    }

    public static function form(Schema $schema): Schema
    {
        return StockItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StockItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockItems::route('/'),
            'create' => CreateStockItem::route('/create'),
            'view' => ViewStockItem::route('/{record}'),
            'edit' => EditStockItem::route('/{record}/edit'),
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
