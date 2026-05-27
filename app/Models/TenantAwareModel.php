<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\TracksUserActions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class TenantAwareModel extends Model
{
    use BelongsToTenant;
    use SoftDeletes;
    use TracksUserActions;

    protected $guarded = [];
}
