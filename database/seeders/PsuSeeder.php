<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\Psu;
use App\Models\Brand;
use App\Models\ComponentType;
use App\Models\Category;

class PsuSeeder extends Seeder
{
    public function run()
    {
        $brandId = Brand::where('name', 'Corsair')->first()?->id;
        $typeId = ComponentType::where('name', 'psu')->first()?->id;
        $categoryId = Category::where('name', 'Alimentation')->first()?->id;

        $component = Component::create([
            'name' => 'Corsair RM850x',
            'brand_id' => $brandId,
            'component_type_id' => $typeId,
            'category_id' => $categoryId,
            'price' => 149.99,
            'description' => '850W, 80+ Gold, entièrement modulaire.',
        ]);

        Psu::create([
            'component_id'   => $component->id,
            'wattage'        => 850,
            'certification'  => '80+ Gold',
            'modular'        => true,
            'form_factor'    => 'ATX',
        ]);
    }
}
