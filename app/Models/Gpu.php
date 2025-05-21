<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gpu extends Model
{
    protected $fillable = [
        'component_id', 'chipset', 'vram', 'base_clock', 'boost_clock', 'tdp', 'length_mm'
    ];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
