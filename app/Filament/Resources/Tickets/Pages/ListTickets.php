<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Concerns\HandlesPrintwayySync;
use App\Filament\Resources\Tickets\TicketResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTickets extends ListRecords
{
    use HandlesPrintwayySync;

    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getPrintwayySyncAction(),
            CreateAction::make(),
        ];
    }
}
