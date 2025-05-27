<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\Motherboard;
use App\Models\Brand;
use App\Models\ComponentType;
use App\Models\Category;

class MotherboardSeeder extends Seeder
{
    public function run()
    {
        $brandId = Brand::where('name', 'ASUS')->first()?->id;
        $typeId = ComponentType::where('name', 'motherboard')->first()?->id;
        $categoryId = Category::where('name', 'Carte mère')->first()?->id;

        $component = Component::create([
            'name' => 'ASUS ROG Strix B550-F Gaming',
            'brand_id' => $brandId,
            'component_type_id' => $typeId,
            'category_id' => $categoryId,
            'price' => 179.99,
            'description' => 'Carte mère ATX socket AM4 pour processeurs Ryzen.',
        ]);

        Motherboard::create([
            'component_id' => $component->id,
            'socket'       => 'AM4',
            'chipset'      => 'B550',
            'form_factor'  => 'ATX',
            'ram_slots'    => 4,
            'max_ram'      => 128,
            'pcie_slots'   => 2,
            'm2_slots'     => 2,
            'sata_ports'   => 6,
        ]);
    }
}
