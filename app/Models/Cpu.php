<?php

// app/Models/Cpu.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasBrand;
class Cpu extends Model
{
    // Pas besoin de 'use Traits\HasBrand;' ici, car la marque est sur le composant.
    // La table par défaut sera 'cpus', ce qui est correct.
    
    protected $fillable = [
        'component_id', 'socket', 'core_count', 'thread_count',
        'base_clock', 'boost_clock', 'tdp', 'integrated_graphics',
    ];

    // Cette relation est maintenant la seule chose qui le lie à Component
    public function component()
    {
        return $this->belongsTo(Component::class, 'component_id');
    }
}
