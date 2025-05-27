<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Storage extends Component
{   
    use Traits\HasBrand;
    protected $fillable = [
        'component_id', 'type', 'capacity_gb', 'interface'
    ];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
