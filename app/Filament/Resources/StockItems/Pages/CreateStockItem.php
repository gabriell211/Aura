<?php

namespace App\Filament\Resources\StockItems\Pages;

use App\Filament\Resources\StockItems\StockItemResource;
use App\Models\StockMovement;
use Filament\Resources\Pages\CreateRecord;

class CreateStockItem extends CreateRecord
{
    protected static string $resource = StockItemResource::class;

    protected function afterCreate(): void
    {
        $currentStock = (int) $this->record->current_stock;

        if ($currentStock < 1) {
            return;
        }

        $movement = $this->record->movements()->create([
            'tenant_id' => (int) $this->record->tenant_id,
            'movement_type' => StockMovement::TYPE_OPENING_BALANCE,
            'quantity' => $currentStock,
            'reason' => 'Saldo inicial informado no cadastro.',
            'moved_at' => now(),
        ]);

        $this->record->update([
            'last_movement_type' => StockMovement::TYPE_OPENING_BALANCE,
            'last_movement_at' => $movement->moved_at,
        ]);
    }
}
