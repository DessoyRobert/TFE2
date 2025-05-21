<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\Gpu;

class GpuSeeder extends Seeder
{
    public function run()
    {
        $component = Component::create([
            'name' => 'Sapphire Radeon RX 7900 XTX Nitro+',
            'brand' => 'Sapphire',
            'type' => 'gpu',
            'price' => 1199.99,
            'img_url' => 'https://example.com/7900xtx.jpg',
            'description' => 'Une carte graphique haut de gamme AMD.',
            'release_year' => 2023,
            'ean' => '1234567890124'
        ]);

        Gpu::create([
            'component_id' => $component->id,
            'chipset' => 'Radeon RX 7900 XTX',
            'vram' => 24,
            'base_clock' => 1855,
            'boost_clock' => 2500,
            'tdp' => 355,
            'length_mm' => 320
        ]);
    }
}
