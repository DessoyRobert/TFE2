<?php

// app/Models/Component.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    protected $fillable = [
        'name', 'brand', 'type', 'price', 'img_url', 'description', 'release_year', 'ean',
    ];

    public function cpu()
    {
        return $this->hasOne(Cpu::class);
    }
    public function gpu()
    {
        return $this->hasOne(Gpu::class);
    }
    public function ram()
    {
        return $this->hasOne(Ram::class);
    }
    public function motherboard()
    {
        return $this->hasOne(Motherboard::class);
    }
    public function storage()
    {
        return $this->hasOne(Storage::class);
    }
    public function psu()
    {
        return $this->hasOne(Psu::class);
    }
    public function cooler()
    {
        return $this->hasOne(Cooler::class);
    }
    public function casemodel()
    {
        return $this->hasOne(CaseModel::class);
    }

    public function builds()
    {
    return $this->belongsToMany(Build::class, 'build_component')
        ->withPivot('quantity')
        ->withTimestamps();
    }


}

