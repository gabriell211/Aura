<?php

namespace App\Filament\Resources\MeterReads;

use App\Filament\Concerns\AuthorizesTabAccess;
use App\Filament\Resources\MeterReads\Pages\ListMeterReads;
use App\Filament\Resources\MeterReads\Tables\MeterReadsTable;
use App\Models\Equipment;
use App\Support\PanelTabs;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class MeterReadResource extends Resource
{
    use AuthorizesTabAccess;

    protected static ?string $model = Equipment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Contadores';

    protected static ?string $modelLabel = 'Contador';

    protected static ?string $pluralModelLabel = 'Contadores';

    protected static string|\UnitEnum|null $navigationGroup = 'Operacional';

    protected static ?int $navigationSort = 35;

    protected static function tabAccessKey(): string
    {
        return PanelTabs::METER_READS;
    }

    public static function table(Table $table): Table
    {
        return MeterReadsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeterReads::route('/'),
        ];
    }
}
