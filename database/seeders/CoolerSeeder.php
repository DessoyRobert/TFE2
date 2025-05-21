<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\Cooler;

class CoolerSeeder extends Seeder
{
    public function run()
    {
        $component = Component::create([
            'name' => 'be quiet! Pure Rock 2',
            'brand' => 'be quiet!',
            'type' => 'cooler',
            'price' => 39.99,
            'img_url' => 'https://example.com/purerock2.jpg',
            'description' => 'Ventirad compatible AM4/LGA1200.',
            'release_year' => 2022,
            'ean' => '1234567890129'
        ]);

        Cooler::create([
            'component_id' => $component->id,
            'type' => 'air',
            'fan_count' => 1,
            'compatible_sockets' => 'AM4,LGA1200',
            'max_tdp' => 150,
            'height_mm' => 155
        ]);
    }
}
