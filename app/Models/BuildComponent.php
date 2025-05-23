<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BuildComponent extends Pivot
{
    protected $table = 'build_components';
    protected $fillable = ['build_id', 'component_id', 'quantity'];
}
