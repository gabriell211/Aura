<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends TenantAwareModel
{
    public const TYPE_OPENING_BALANCE = 'opening_balance';

    public const TYPE_INBOUND = 'inbound';

    public const TYPE_OUTBOUND = 'outbound';

    public const TYPE_RESERVATION = 'reservation';

    public const TYPE_INSTALLATION = 'installation';

    public const TYPE_RETURN = 'return';

    public const TYPE_DISPOSAL = 'disposal';

    public const TYPE_RECONDITIONING = 'reconditioning';

    public const TYPE_ADJUSTMENT_IN = 'adjustment_in';

    public const TYPE_ADJUSTMENT_OUT = 'adjustment_out';

    protected $casts = [
        'moved_at' => 'datetime',
    ];

    /**
     * @return array<string, string>
     */
    public static function movementTypeOptions(): array
    {
        return [
            self::TYPE_INBOUND => 'Entrada',
            self::TYPE_OUTBOUND => 'Saida',
            self::TYPE_RESERVATION => 'Reserva',
            self::TYPE_INSTALLATION => 'Instalacao',
            self::TYPE_RETURN => 'Devolucao',
            self::TYPE_RECONDITIONING => 'Recondicionamento',
            self::TYPE_DISPOSAL => 'Descarte',
            self::TYPE_ADJUSTMENT_IN => 'Ajuste positivo',
            self::TYPE_ADJUSTMENT_OUT => 'Ajuste negativo',
        ];
    }

    public static function movementTypeLabel(?string $type): string
    {
        $allTypes = self::movementTypeOptions() + [
            self::TYPE_OPENING_BALANCE => 'Saldo inicial',
        ];

        return $allTypes[$type ?? ''] ?? 'Movimentacao';
    }

    public static function movementTypeColor(?string $type): string
    {
        return match ($type) {
            self::TYPE_INBOUND,
            self::TYPE_RETURN,
            self::TYPE_RECONDITIONING,
            self::TYPE_ADJUSTMENT_IN,
            self::TYPE_OPENING_BALANCE => 'success',
            self::TYPE_OUTBOUND,
            self::TYPE_INSTALLATION,
            self::TYPE_RESERVATION,
            self::TYPE_ADJUSTMENT_OUT => 'warning',
            self::TYPE_DISPOSAL => 'danger',
            default => 'gray',
        };
    }

    public static function directionForType(string $type): int
    {
        return match ($type) {
            self::TYPE_INBOUND,
            self::TYPE_RETURN,
            self::TYPE_RECONDITIONING,
            self::TYPE_ADJUSTMENT_IN,
            self::TYPE_OPENING_BALANCE => 1,
            self::TYPE_OUTBOUND,
            self::TYPE_RESERVATION,
            self::TYPE_INSTALLATION,
            self::TYPE_DISPOSAL,
            self::TYPE_ADJUSTMENT_OUT => -1,
            default => 0,
        };
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }
}
