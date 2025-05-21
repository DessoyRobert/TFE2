<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\Psu;

class PsuSeeder extends Seeder
{
    public function run()
    {
        $component = Component::create([
            'name' => 'Corsair RM850x',
            'brand' => 'Corsair',
            'type' => 'psu',
            'price' => 139.99,
            'img_url' => 'https://example.com/rm850x.jpg',
            'description' => 'Alimentation modulaire 80+ Gold 850W.',
            'release_year' => 2023,
            'ean' => '1234567890128'
        ]);

        Psu::create([
            'component_id' => $component->id,
            'wattage' => 850,
            'certification' => '80+ Gold',
            'modular' => true,
            'form_factor' => 'ATX'
        ]);
    }
}
