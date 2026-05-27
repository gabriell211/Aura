<?php

namespace App\Filament\Resources\TechnicianOrders\Pages;

use App\Filament\Resources\TechnicianOrders\TechnicianOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListTechnicianOrders extends ListRecords
{
    protected static string $resource = TechnicianOrderResource::class;
}
