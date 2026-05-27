<?php

namespace App\Filament\Resources\TechnicianOrders\Pages;

use App\Filament\Resources\TechnicianOrders\TechnicianOrderResource;
use Filament\Resources\Pages\ViewRecord;

class ViewTechnicianOrder extends ViewRecord
{
    protected static string $resource = TechnicianOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
