<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends TenantAwareModel
{
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'reading_start_date' => 'date',
            'reading_end_date' => 'date',
            'monthly_fee' => 'float',
            'bw_overage_price' => 'float',
            'color_overage_price' => 'float',
            'global_bw_franchise_value' => 'float',
            'global_color_franchise_value' => 'float',
            'allow_extension' => 'boolean',
            'show_observation' => 'boolean',
            'issue_boleto' => 'boolean',
            'unified_boleto' => 'boolean',
            'unified_contract' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function clientUnit(): BelongsTo
    {
        return $this->belongsTo(ClientUnit::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }

    public function franquias(): HasMany
    {
        return $this->hasMany(ContractFranquia::class);
    }

    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }
}
