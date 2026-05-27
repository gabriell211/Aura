<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'printwayy_enabled' => 'boolean',
            'printwayy_api_token' => 'encrypted',
            'printwayy_last_sync_at' => 'datetime',
            'trial_starts_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'trial_last_notice_at' => 'datetime',
            'infinitepay_checkout_generated_at' => 'datetime',
            'trial_converted_at' => 'datetime',
            'trial_expired_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
