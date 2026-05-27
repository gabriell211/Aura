<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends TenantAwareModel
{
    public const ACQUISITION_NEW = 'new';

    public const ACQUISITION_RECONDITIONED = 'reconditioned';

    protected function casts(): array
    {
        return [
            'installed_at' => 'datetime',
            'is_backup' => 'boolean',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function acquisitionTypeOptions(): array
    {
        return [
            self::ACQUISITION_NEW => 'Nova',
            self::ACQUISITION_RECONDITIONED => 'Recondicionada',
        ];
    }

    public static function acquisitionTypeLabel(?string $type): string
    {
        $options = self::acquisitionTypeOptions();

        return $options[$type ?? ''] ?? 'Nao informado';
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function clientUnit(): BelongsTo
    {
        return $this->belongsTo(ClientUnit::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function meterReads(): HasMany
    {
        return $this->hasMany(MeterRead::class);
    }

    public function latestMeterRead(): HasOne
    {
        return $this->hasOne(MeterRead::class)->latestOfMany('read_at');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(EquipmentLog::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(EquipmentAlert::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function partUsages(): HasMany
    {
        return $this->hasMany(EquipmentPartUsage::class)->latest('used_at');
    }
}
