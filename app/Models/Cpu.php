<?php

// app/Models/Cpu.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasBrand;
class Cpu extends Component
{   
    use Traits\HasBrand;
    protected $fillable = [
        'component_id', 'socket', 'core_count', 'thread_count',
        'base_clock', 'boost_clock', 'tdp', 'integrated_graphics',
    ];


    public function component()
    {
    return $this->belongsTo(Component::class);
    }

}

