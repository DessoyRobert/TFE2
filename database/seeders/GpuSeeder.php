<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\Gpu;
use App\Models\Brand;
use App\Models\ComponentType;
use App\Models\Category;

class GpuSeeder extends Seeder
{
    public function run()
    {
        $brandId = Brand::where('name', 'Sapphire')->first()?->id;
        $typeId = ComponentType::where('name', 'gpu')->first()?->id;
        $categoryId = Category::where('name', 'Carte graphique')->first()?->id;

        $component = Component::create([
            'name' => 'Sapphire Radeon RX 7900 XTX Nitro+',
            'brand_id' => $brandId,
            'component_type_id' => $typeId,
            'category_id' => $categoryId,
            'price' => 1199.99,
            'img_url' => 'https://example.com/7900xtx.jpg',
            'description' => 'Une carte graphique haut de gamme AMD.',
            'release_year' => 2023,
            'ean' => '1234567890124',
        ]);

        Gpu::create([
            'component_id' => $component->id,
            'chipset' => 'RX 7900 XTX',
            'memory' => '24GB',
            'base_clock' => 1855,
            'boost_clock' => 2500,
        ]);
    }
}
