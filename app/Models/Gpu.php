<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gpu extends Component
{   
    use Traits\HasBrand;
    protected $fillable = [
        'component_id',
        'chipset',
        'memory',
        'base_clock',
        'boost_clock',
    ];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
