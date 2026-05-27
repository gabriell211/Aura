<?php

namespace App\Filament\Resources\MeterReads\Pages;

use App\Filament\Concerns\HandlesPrintwayySync;
use App\Filament\Resources\MeterReads\MeterReadResource;
use Filament\Resources\Pages\ListRecords;

class ListMeterReads extends ListRecords
{
    use HandlesPrintwayySync;

    protected static string $resource = MeterReadResource::class;
    
    protected static ?string $title = 'Contadores';

    protected function getHeaderActions(): array
    {
        return [
            $this->getPrintwayySyncAction(),
        ];
    }
}
