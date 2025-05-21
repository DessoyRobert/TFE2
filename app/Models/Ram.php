<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ram extends Model
{
    protected $fillable = [
        'component_id', 'type', 'capacity_gb', 'modules', 'speed_mhz', 'cas_latency'
    ];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
