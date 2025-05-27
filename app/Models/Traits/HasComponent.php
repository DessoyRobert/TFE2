<?php

namespace App\Models\Traits;

use App\Models\Cpu;
use App\Models\Gpu;
use App\Models\Ram;
use App\Models\Storage;
use App\Models\Psu;
use App\Models\CaseModel;
use App\Models\Cooler;
use App\Models\Motherboard;

trait HasComponents
{
    public function cpus()
    {
        return $this->hasMany(Cpu::class);
    }

    public function gpus()
    {
        return $this->hasMany(Gpu::class);
    }

    public function rams()
    {
        return $this->hasMany(Ram::class);
    }

    public function storages()
    {
        return $this->hasMany(Storage::class);
    }

    public function psus()
    {
        return $this->hasMany(Psu::class);
    }

    public function cases()
    {
        return $this->hasMany(CaseModel::class);
    }

    public function coolers()
    {
        return $this->hasMany(Cooler::class);
    }

    public function motherboards()
    {
        return $this->hasMany(Motherboard::class);
    }
}
