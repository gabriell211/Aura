<?php

namespace App\DTOs;

final readonly class MeterReadData
{
    public function __construct(
        public int $tenantId,
        public int $equipmentId,
        public string $readAt,
        public int $monoTotal,
        public int $colorTotal,
        public string $source,
        public array $rawPayload = [],
    ) {
    }

    public function totalPages(): int
    {
        return $this->monoTotal + $this->colorTotal;
    }
}
