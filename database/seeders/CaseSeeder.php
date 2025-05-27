<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\CaseModel;
use App\Models\Brand;
use App\Models\ComponentType;
use App\Models\Category;

class CaseSeeder extends Seeder
{
    public function run()
    {
        $brandId = Brand::where('name', 'NZXT')->first()?->id;
        $typeId = ComponentType::where('name', 'case')->first()?->id;
        $categoryId = Category::where('name', 'Boîtier')->first()?->id;

        $component = Component::create([
            'name' => 'NZXT H510',
            'brand_id' => $brandId,
            'component_type_id' => $typeId,
            'category_id' => $categoryId,
            'price' => 84.99,
            'img_url' => 'https://example.com/h510.jpg',
            'description' => 'Boîtier moyen tour ATX moderne.',
            'release_year' => 2023,
            'ean' => '1234567890130'
        ]);

        CaseModel::create([
            'component_id' => $component->id,
            'form_factor' => 'ATX',
            'max_gpu_length' => 381,
            'max_cooler_height' => 165,
            'psu_form_factor' => 'ATX',
            'fan_mounts' => 4
        ]);
    }
}
