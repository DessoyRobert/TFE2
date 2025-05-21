<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\Storage;

class StorageSeeder extends Seeder
{
    public function run()
    {
        $component = Component::create([
            'name' => 'Samsung 970 EVO Plus 1TB',
            'brand' => 'Samsung',
            'type' => 'storage',
            'price' => 89.99,
            'img_url' => 'https://example.com/970evoplus.jpg',
            'description' => 'SSD NVMe PCIe 3.0, 1TB.',
            'release_year' => 2022,
            'ean' => '1234567890127'
        ]);

        Storage::create([
            'component_id' => $component->id,
            'type' => 'NVMe',
            'capacity_gb' => 1000,
            'interface' => 'PCIe 3.0'
        ]);
    }
}
