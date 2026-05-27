<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StockItem extends TenantAwareModel
{
    public const LIFECYCLE_STAGE_IN_STOCK = 'in_stock';

    public const LIFECYCLE_STAGE_RESERVED = 'reserved';

    public const LIFECYCLE_STAGE_INSTALLED = 'installed';

    public const LIFECYCLE_STAGE_IN_MAINTENANCE = 'in_maintenance';

    public const LIFECYCLE_STAGE_RECONDITIONED = 'reconditioned';

    public const LIFECYCLE_STAGE_DISPOSED = 'disposed';

    protected $casts = [
        'last_movement_at' => 'datetime',
    ];

    /**
     * @return array<string, string>
     */
    public static function lifecycleStageOptions(): array
    {
        return [
            self::LIFECYCLE_STAGE_IN_STOCK => 'Em estoque',
            self::LIFECYCLE_STAGE_RESERVED => 'Reservado',
            self::LIFECYCLE_STAGE_INSTALLED => 'Instalado',
            self::LIFECYCLE_STAGE_IN_MAINTENANCE => 'Em manutencao',
            self::LIFECYCLE_STAGE_RECONDITIONED => 'Recondicionado',
            self::LIFECYCLE_STAGE_DISPOSED => 'Descartado',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function lifecycleStageColors(): array
    {
        return [
            self::LIFECYCLE_STAGE_IN_STOCK => 'success',
            self::LIFECYCLE_STAGE_RESERVED => 'warning',
            self::LIFECYCLE_STAGE_INSTALLED => 'info',
            self::LIFECYCLE_STAGE_IN_MAINTENANCE => 'danger',
            self::LIFECYCLE_STAGE_RECONDITIONED => 'primary',
            self::LIFECYCLE_STAGE_DISPOSED => 'gray',
        ];
    }

    public static function lifecycleStageLabel(?string $stage): string
    {
        $options = self::lifecycleStageOptions();

        return $options[$stage ?? ''] ?? 'Nao informado';
    }

    public static function lifecycleStageColor(?string $stage): string
    {
        $colors = self::lifecycleStageColors();

        return $colors[$stage ?? ''] ?? 'gray';
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest('moved_at');
    }

    public function latestMovement(): HasOne
    {
        return $this->hasOne(StockMovement::class)->latestOfMany('moved_at');
    }

    public function equipmentUsages(): HasMany
    {
        return $this->hasMany(EquipmentPartUsage::class)->latest('used_at');
    }
}
