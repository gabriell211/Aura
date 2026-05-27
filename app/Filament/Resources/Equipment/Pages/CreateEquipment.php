<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Services\EquipmentPartUsageService;
use Filament\Resources\Pages\CreateRecord;

class CreateEquipment extends CreateRecord
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
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->partUsagePayload = is_array($data['part_usages'] ?? null)
            ? $data['part_usages']
            : [];

        unset($data['part_usages']);

        return $data;
    }

    protected function afterCreate(): void
    {
        app(EquipmentPartUsageService::class)->syncForEquipment(
            equipment: $this->record,
            partUsages: $this->partUsagePayload,
            acquisitionType: (string) ($this->record->acquisition_type ?? ''),
        );
    }
}
