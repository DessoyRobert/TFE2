<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StorageSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $typeId = DB::table('component_types')->where('name', 'storage')->value('id');
        $categoryId = DB::table('categories')->where('name', 'like', '%storage%')->value('id');

        $storages = [
            ['Samsung 970 EVO Plus', 'NVMe', 1000, 'PCIe Gen3 x4', 'Samsung'],
            ['WD Blue SN570', 'NVMe', 500, 'PCIe Gen3 x4', 'Western Digital'],
            ['Crucial MX500', 'SSD', 1000, 'SATA III', 'Crucial'],
            ['Seagate Barracuda', 'HDD', 2000, 'SATA III', 'Seagate'],
            ['WD Black SN850X', 'NVMe', 2000, 'PCIe Gen4 x4', 'Western Digital'],
            ['Kingston A400', 'SSD', 480, 'SATA III', 'Kingston'],
            ['Toshiba P300', 'HDD', 1000, 'SATA III', 'Toshiba'],
            ['ADATA XPG SX8200 Pro', 'NVMe', 1000, 'PCIe Gen3 x4', 'ADATA'],
            ['Samsung 870 QVO', 'SSD', 2000, 'SATA III', 'Samsung'],
            ['Seagate FireCuda 530', 'NVMe', 1000, 'PCIe Gen4 x4', 'Seagate'],
        ];

        foreach ($storages as [$name, $type, $capacity, $interface, $brandName]) {
            $brandId = DB::table('brands')->where('name', 'like', "%$brandName%")->value('id') ?? 1;

            $componentId = DB::table('components')->insertGetId([
                'name' => $name,
                'component_type_id' => $typeId,
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('storages')->insert([
                'component_id' => $componentId,
                'type' => $type,
                'capacity_gb' => $capacity,
                'interface' => $interface,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
