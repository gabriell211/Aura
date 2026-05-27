<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\EquipmentPartUsage;
use App\Services\EquipmentPartUsageService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEquipment extends EditRecord
{
    protected static string $resource = EquipmentResource::class;

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $partUsagePayload = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['part_usages'] = $this->getRecord()
            ->partUsages()
            ->orderBy('id')
            ->get()
            ->map(fn (EquipmentPartUsage $usage): array => [
                'stock_item_id' => (int) $usage->stock_item_id,
                'quantity' => (int) $usage->quantity,
                'notes' => $usage->notes,
            ])
            ->all();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->partUsagePayload = is_array($data['part_usages'] ?? null)
            ? $data['part_usages']
            : [];

        unset($data['part_usages']);

        return $data;
    }

    protected function afterSave(): void
    {
        app(EquipmentPartUsageService::class)->syncForEquipment(
            equipment: $this->getRecord(),
            partUsages: $this->partUsagePayload,
            acquisitionType: (string) ($this->getRecord()->acquisition_type ?? ''),
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
