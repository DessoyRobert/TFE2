<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\Ram;

class RamSeeder extends Seeder
{
    public function run()
    {
        $component = Component::create([
            'name' => 'Corsair Vengeance LPX 16GB',
            'brand' => 'Corsair',
            'type' => 'ram',
            'price' => 69.99,
            'img_url' => 'https://example.com/vengeance.jpg',
            'description' => '16GB (2x8GB) DDR4 3200MHz CL16.',
            'release_year' => 2022,
            'ean' => '1234567890125'
        ]);

        Ram::create([
            'component_id' => $component->id,
            'type' => 'DDR4',
            'capacity_gb' => 16,
            'modules' => 2,
            'speed_mhz' => 3200,
            'cas_latency' => 'CL16'
        ]);
    }
}
