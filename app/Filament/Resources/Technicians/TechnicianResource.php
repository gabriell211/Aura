<?php

namespace App\Filament\Resources\Technicians;

use App\Filament\Concerns\AuthorizesTabAccess;
use App\Filament\Resources\Technicians\Pages\CreateTechnician;
use App\Filament\Resources\Technicians\Pages\EditTechnician;
use App\Filament\Resources\Technicians\Pages\ListTechnicians;
use App\Filament\Resources\Technicians\Pages\ViewTechnician;
use App\Filament\Resources\Technicians\Schemas\TechnicianForm;
use App\Filament\Resources\Technicians\Schemas\TechnicianInfolist;
use App\Filament\Resources\Technicians\Tables\TechniciansTable;
use App\Models\Technician;
use App\Support\PanelTabs;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TechnicianResource extends Resource
{
    use AuthorizesTabAccess;

    protected static ?string $model = Technician::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $navigationLabel = 'Tecnicos';

    protected static ?string $modelLabel = 'Tecnico';

    protected static ?string $pluralModelLabel = 'Tecnicos';

    protected static string | \UnitEnum | null $navigationGroup = 'SLA & Suporte';

    protected static ?int $navigationSort = 35;

    protected static ?string $slug = 'tecnicos';

    protected static function tabAccessKey(): string
    {
        return PanelTabs::TECHNICIANS;
    }

    public static function form(Schema $schema): Schema
    {
        return TechnicianForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TechnicianInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TechniciansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTechnicians::route('/'),
            'create' => CreateTechnician::route('/create'),
            'view' => ViewTechnician::route('/{record}'),
            'edit' => EditTechnician::route('/{record}/edit'),
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
