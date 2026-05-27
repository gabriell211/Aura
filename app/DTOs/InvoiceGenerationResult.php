<?php

namespace App\DTOs;

final readonly class InvoiceGenerationResult
{
    public function __construct(
        public int $invoiceId,
        public string $reference,
        public float $subtotal,
        public float $excessTotal,
        public float $grandTotal,
        public int $bwUsagePages = 0,
        public int $colorUsagePages = 0,
        public int $bwOveragePages = 0,
        public int $colorOveragePages = 0,
        public bool $anomalyDetected = false,
    ) {
    }
}
