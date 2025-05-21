<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cooler extends Model
{
    protected $fillable = [
        'component_id', 'type', 'fan_count', 'compatible_sockets', 'max_tdp', 'height_mm'
    ];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
