<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generatePdf')
                ->label('Gerar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn (): string => route('admin.tickets.pdf', ['ticket' => $this->getRecord()]))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
