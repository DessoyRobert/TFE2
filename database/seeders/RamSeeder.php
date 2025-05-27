<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\Ram;
use App\Models\Brand;
use App\Models\ComponentType;
use App\Models\Category;

class RamSeeder extends Seeder
{
    public function run()
    {
        $brandId = Brand::where('name', 'Corsair')->first()?->id;
        $typeId = ComponentType::where('name', 'ram')->first()?->id;
        $categoryId = Category::where('name', 'Mémoire')->first()?->id;

        $component = Component::create([
            'name' => 'Corsair Vengeance 16GB DDR4',
            'brand_id' => $brandId,
            'component_type_id' => $typeId,
            'category_id' => $categoryId,
            'price' => 89.99,
            'description' => '3200 MHz CL16, kit 2x8GB.',
        ]);

        Ram::create([
            'component_id' => $component->id,
            'capacity_gb' => 16,
            'speed_mhz' => 3200,
            'modules' => 2,
            'type' => 'DDR4',
        ]);
    }
}
