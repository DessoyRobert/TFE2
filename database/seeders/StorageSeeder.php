<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\Storage;
use App\Models\Brand;
use App\Models\ComponentType;
use App\Models\Category;

class StorageSeeder extends Seeder
{
    public function run()
    {
        $brandId = Brand::where('name', 'Samsung')->first()?->id;
        $typeId = ComponentType::where('name', 'storage')->first()?->id;
        $categoryId = Category::where('name', 'Stockage')->first()?->id;

        $component = Component::create([
            'name' => 'Samsung 980 PRO 1TB NVMe',
            'brand_id' => $brandId,
            'component_type_id' => $typeId,
            'category_id' => $categoryId,
            'price' => 129.99,
            'description' => 'SSD NVMe Gen4 ultra rapide.',
        ]);

        Storage::create([
            'component_id' => $component->id,
            'type'         => 'NVMe',
            'capacity_gb'  => 1024,
            'interface'    => 'PCIe 4.0 x4',
        ]);
    }
}
