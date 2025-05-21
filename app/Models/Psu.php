<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Psu extends Model
{
    protected $fillable = [
        'component_id', 'wattage', 'certification', 'modular', 'form_factor'
    ];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
