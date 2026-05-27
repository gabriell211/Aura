<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generatePdf')
                ->label('Gerar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn (): string => route('admin.invoices.pdf', ['invoice' => $this->getRecord()]))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
