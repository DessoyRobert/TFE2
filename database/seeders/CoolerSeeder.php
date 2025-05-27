<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\Cooler;
use App\Models\Brand;
use App\Models\ComponentType;
use App\Models\Category;

class CoolerSeeder extends Seeder
{
    public function run()
    {
        $brandId = Brand::where('name', 'Noctua')->first()?->id;
        $typeId = ComponentType::where('name', 'cooler')->first()?->id;
        $categoryId = Category::where('name', 'Refroidisseur')->first()?->id;

        $component = Component::create([
            'name' => 'Noctua NH-D15',
            'brand_id' => $brandId,
            'component_type_id' => $typeId,
            'category_id' => $categoryId,
            'price' => 99.99,
            'description' => 'Air cooler double tour ultra performant.',
        ]);

        Cooler::create([
            'component_id'        => $component->id,
            'type'                => 'Air',
            'fan_count'           => 2,
            'compatible_sockets'  => 'AM4, LGA1700, LGA1200, LGA115x',
            'max_tdp'             => 220,
            'height_mm'           => 165,
        ]);
    }
}
