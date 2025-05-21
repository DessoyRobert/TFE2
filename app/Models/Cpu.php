<?php

// app/Models/Cpu.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cpu extends Model
{
    protected $fillable = [
        'component_id', 'socket', 'core_count', 'thread_count',
        'base_clock', 'boost_clock', 'tdp', 'integrated_graphics',
    ];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}

