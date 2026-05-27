<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\EquipmentPartUsage;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EquipmentPartUsageService
{
    public function __construct(
        private readonly StockLifecycleService $stockLifecycleService,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $partUsages
     */
    public function syncForEquipment(Equipment $equipment, array $partUsages, string $acquisitionType): void
    {
        $normalizedPartUsages = $this->normalizePartUsages($partUsages);

        if ($acquisitionType !== Equipment::ACQUISITION_RECONDITIONED) {
            $normalizedPartUsages = [];
        }

        DB::transaction(function () use ($equipment, $normalizedPartUsages, $acquisitionType): void {
            /** @var Equipment|null $lockedEquipment */
            $lockedEquipment = Equipment::query()
                ->whereKey($equipment->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedEquipment === null) {
                throw ValidationException::withMessages([
                    'equipment' => 'Equipamento nao encontrado.',
                ]);
            }

            $existingRows = $lockedEquipment->partUsages()
                ->get(['stock_item_id', 'quantity']);

            $existingByStockItem = $this->aggregateByStockItem(
                $existingRows
                    ->map(fn (EquipmentPartUsage $row): array => [
                        'stock_item_id' => (int) $row->stock_item_id,
                        'quantity' => (int) $row->quantity,
                    ])
                    ->all(),
            );

            $targetByStockItem = $this->aggregateByStockItem($normalizedPartUsages);

            $stockItemIds = array_values(array_unique([
                ...array_map('intval', array_keys($existingByStockItem)),
                ...array_map('intval', array_keys($targetByStockItem)),
            ]));

            sort($stockItemIds);

            foreach ($stockItemIds as $stockItemId) {
                $currentQuantity = $existingByStockItem[$stockItemId] ?? 0;
                $targetQuantity = $targetByStockItem[$stockItemId] ?? 0;
                $delta = $targetQuantity - $currentQuantity;

                if ($delta === 0) {
                    continue;
                }

                /** @var StockItem|null $stockItem */
                $stockItem = StockItem::query()
                    ->where('tenant_id', (int) $lockedEquipment->tenant_id)
                    ->whereKey($stockItemId)
                    ->first();

                if ($stockItem === null) {
                    throw ValidationException::withMessages([
                        'part_usages' => "Peca de estoque #{$stockItemId} nao encontrada para esta empresa.",
                    ]);
                }

                if ($delta > 0) {
                    $this->stockLifecycleService->registerMovement(
                        stockItem: $stockItem,
                        movementType: StockMovement::TYPE_INSTALLATION,
                        quantity: $delta,
                        reason: $this->buildMovementReason($lockedEquipment, $acquisitionType, false),
                        movedAt: now()->toDateTimeString(),
                        lifecycleStage: null,
                        referenceType: Equipment::class,
                        referenceId: (int) $lockedEquipment->getKey(),
                    );

                    continue;
                }

                $this->stockLifecycleService->registerMovement(
                    stockItem: $stockItem,
                    movementType: StockMovement::TYPE_RETURN,
                    quantity: abs($delta),
                    reason: $this->buildMovementReason($lockedEquipment, $acquisitionType, true),
                    movedAt: now()->toDateTimeString(),
                    lifecycleStage: null,
                    referenceType: Equipment::class,
                    referenceId: (int) $lockedEquipment->getKey(),
                );
            }

            $lockedEquipment->partUsages()->delete();

            foreach ($normalizedPartUsages as $usageRow) {
                $lockedEquipment->partUsages()->create([
                    'tenant_id' => (int) $lockedEquipment->tenant_id,
                    'stock_item_id' => (int) $usageRow['stock_item_id'],
                    'quantity' => (int) $usageRow['quantity'],
                    'notes' => $usageRow['notes'] ?? null,
                    'used_at' => $usageRow['used_at'] ?? ($lockedEquipment->installed_at?->toDateTimeString() ?? now()->toDateTimeString()),
                ]);
            }
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $partUsages
     * @return array<int, array{stock_item_id: int, quantity: int, notes: string|null, used_at: string|null}>
     */
    private function normalizePartUsages(array $partUsages): array
    {
        $normalized = [];

        foreach ($partUsages as $row) {
            $stockItemId = (int) ($row['stock_item_id'] ?? 0);
            $quantity = (int) ($row['quantity'] ?? 0);

            if ($stockItemId < 1 || $quantity < 1) {
                continue;
            }

            $notes = isset($row['notes']) ? trim((string) $row['notes']) : null;
            $usedAt = isset($row['used_at']) && filled($row['used_at']) ? (string) $row['used_at'] : null;

            $normalized[] = [
                'stock_item_id' => $stockItemId,
                'quantity' => $quantity,
                'notes' => filled($notes) ? $notes : null,
                'used_at' => $usedAt,
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<int, array<string, mixed>>  $partUsages
     * @return array<int, int>
     */
    private function aggregateByStockItem(array $partUsages): array
    {
        $totals = [];

        foreach ($partUsages as $row) {
            $stockItemId = (int) ($row['stock_item_id'] ?? 0);
            $quantity = (int) ($row['quantity'] ?? 0);

            if ($stockItemId < 1 || $quantity < 1) {
                continue;
            }

            $totals[$stockItemId] = ($totals[$stockItemId] ?? 0) + $quantity;
        }

        return $totals;
    }

    private function buildMovementReason(Equipment $equipment, string $acquisitionType, bool $isRollback): string
    {
        $typeLabel = $acquisitionType === Equipment::ACQUISITION_RECONDITIONED ? 'recondicionada' : 'nova';
        $serial = (string) $equipment->serial_number;

        if ($isRollback) {
            return "Ajuste de pecas da maquina {$typeLabel} ({$serial}).";
        }

        return "Uso de pecas em maquina {$typeLabel} ({$serial}).";
    }
}
