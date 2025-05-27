<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseModel extends Component
{   
    use Traits\HasBrand;
    protected $fillable = [
        'component_id', 'form_factor', 'max_gpu_length',
        'max_cooler_height', 'psu_form_factor', 'fan_mounts'
    ];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
