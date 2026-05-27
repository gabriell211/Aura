<?php

namespace App\Services;

use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockLifecycleService
{
    public function registerMovement(
        StockItem $stockItem,
        string $movementType,
        int $quantity,
        ?string $reason = null,
        ?string $movedAt = null,
        ?string $lifecycleStage = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): StockMovement {
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'A quantidade deve ser maior que zero.',
            ]);
        }

        $direction = StockMovement::directionForType($movementType);

        if ($direction === 0) {
            throw ValidationException::withMessages([
                'movement_type' => 'Tipo de movimentacao invalido.',
            ]);
        }

        return DB::transaction(function () use ($stockItem, $movementType, $quantity, $reason, $movedAt, $lifecycleStage, $referenceType, $referenceId): StockMovement {
            /** @var StockItem|null $lockedItem */
            $lockedItem = StockItem::query()
                ->whereKey($stockItem->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedItem === null) {
                throw ValidationException::withMessages([
                    'stock_item' => 'Item de estoque nao encontrado.',
                ]);
            }

            $signedQuantity = $quantity * StockMovement::directionForType($movementType);
            $newStock = (int) $lockedItem->current_stock + $signedQuantity;

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Movimentacao invalida: estoque nao pode ficar negativo.',
                ]);
            }

            /** @var StockMovement $movement */
            $movement = $lockedItem->movements()->create([
                'tenant_id' => (int) $lockedItem->tenant_id,
                'movement_type' => $movementType,
                'quantity' => $signedQuantity,
                'reason' => filled($reason) ? trim((string) $reason) : null,
                'reference_type' => filled($referenceType) ? trim((string) $referenceType) : null,
                'reference_id' => $referenceId,
                'moved_at' => filled($movedAt) ? $movedAt : now()->toDateTimeString(),
            ]);

            $lockedItem->current_stock = $newStock;
            $lockedItem->last_movement_type = $movementType;
            $lockedItem->last_movement_at = $movement->moved_at;

            if (filled($lifecycleStage)) {
                $lockedItem->lifecycle_stage = (string) $lifecycleStage;
            } elseif ($movementType === StockMovement::TYPE_DISPOSAL && $newStock === 0) {
                $lockedItem->lifecycle_stage = StockItem::LIFECYCLE_STAGE_DISPOSED;
            } elseif ($movementType === StockMovement::TYPE_INSTALLATION) {
                $lockedItem->lifecycle_stage = StockItem::LIFECYCLE_STAGE_INSTALLED;
            } elseif ($movementType === StockMovement::TYPE_RESERVATION) {
                $lockedItem->lifecycle_stage = StockItem::LIFECYCLE_STAGE_RESERVED;
            } elseif (in_array($movementType, [StockMovement::TYPE_INBOUND, StockMovement::TYPE_RETURN, StockMovement::TYPE_ADJUSTMENT_IN], true)) {
                $lockedItem->lifecycle_stage = StockItem::LIFECYCLE_STAGE_IN_STOCK;
            } elseif ($movementType === StockMovement::TYPE_RECONDITIONING) {
                $lockedItem->lifecycle_stage = StockItem::LIFECYCLE_STAGE_RECONDITIONED;
            }

            $lockedItem->save();

            return $movement;
        });
    }

    public function updateLifecycleStage(StockItem $stockItem, string $stage, ?string $notes = null): void
    {
        if (! array_key_exists($stage, StockItem::lifecycleStageOptions())) {
            throw ValidationException::withMessages([
                'lifecycle_stage' => 'Estagio de ciclo de vida invalido.',
            ]);
        }

        $stockItem->update([
            'lifecycle_stage' => $stage,
            'lifecycle_notes' => filled($notes) ? trim((string) $notes) : null,
        ]);
    }
}
