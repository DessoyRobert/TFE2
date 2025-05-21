<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Motherboard extends Model
{
    protected $fillable = [
        'component_id', 'socket', 'chipset', 'form_factor',
        'ram_slots', 'max_ram', 'pcie_slots', 'm2_slots', 'sata_ports'
    ];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
