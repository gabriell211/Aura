<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\InvoiceItem;

class InvoiceRepository
{
    public function findByContractAndReference(int $contractId, string $reference): ?Invoice
    {
        return Invoice::query()
            ->where('contract_id', $contractId)
            ->where('billing_reference', $reference)
            ->first();
    }

    public function create(array $attributes): Invoice
    {
        return Invoice::query()->create($attributes);
    }

    public function update(Invoice $invoice, array $attributes): Invoice
    {
        $invoice->fill($attributes);
        $invoice->save();

        return $invoice->refresh();
    }

    public function replaceItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();

        foreach ($items as $item) {
            InvoiceItem::query()->create($item + [
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
            ]);
        }
    }
}
