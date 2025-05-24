<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\Cpu;

class ComponentSeeder extends Seeder
{   
    
    public function run()
    {   
        $cpuTypeId = ComponentType::where('name', 'cpu')->first()->id;
        // Ex: Ajout d'un CPU
        $component = Component::create([
            'name' => 'AMD Ryzen 7 5800X',
            'brand' => 'AMD',
            'component_type_id' => $cpuTypeId, 
            'price' => 319.99,
            'img_url' => 'https://example.com/ryzen7.jpg',
            'description' => 'Un excellent processeur gaming.',
            'release_year' => 2021,
            'ean' => '1234567890123'
        ]);

        Cpu::create([
            'component_id' => $component->id,
            'socket' => 'AM4',
            'core_count' => 8,
            'thread_count' => 16,
            'base_clock' => 3.8,
            'boost_clock' => 4.7,
            'tdp' => 105,
            'integrated_graphics' => null
        ]);
    }
}
