<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\Motherboard;

class MotherboardSeeder extends Seeder
{
    public function run()
    {
        $component = Component::create([
            'name' => 'Asus Prime X570-P',
            'brand' => 'Asus',
            'type' => 'motherboard',
            'price' => 189.99,
            'img_url' => 'https://example.com/x570p.jpg',
            'description' => 'Carte mère ATX pour Ryzen avec chipset X570.',
            'release_year' => 2021,
            'ean' => '1234567890126'
        ]);

        Motherboard::create([
            'component_id' => $component->id,
            'socket' => 'AM4',
            'chipset' => 'X570',
            'form_factor' => 'ATX',
            'ram_slots' => 4,
            'max_ram' => 128,
            'pcie_slots' => 3,
            'm2_slots' => 2,
            'sata_ports' => 6
        ]);
    }
}
