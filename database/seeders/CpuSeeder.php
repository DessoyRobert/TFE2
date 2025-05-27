<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\Cpu;
use App\Models\Brand;
use App\Models\ComponentType;
use App\Models\Category;

class CpuSeeder extends Seeder
{
    public function run()
    {
        $brandId = Brand::where('name', 'AMD')->first()?->id;
        $typeId = ComponentType::where('name', 'cpu')->first()?->id;
        $categoryId = Category::where('name', 'Processeur')->first()?->id;

        $component = Component::create([
            'name' => 'AMD Ryzen 7 5800X',
            'brand_id' => $brandId,
            'component_type_id' => $typeId,
            'category_id' => $categoryId,
            'price' => 289.99,
            'description' => '8 cores, 16 threads, 3.8 GHz base clock.',
        ]);

        Cpu::create([
            'component_id' => $component->id,
            'socket' => 'AM4',
            'core_count' => 8,
            'thread_count' => 16,
            'base_clock' => 3.8,
            'boost_clock' => 4.7,
            'tdp' => 105,
            'integrated_graphics' => null,
        ]);
    }
}
