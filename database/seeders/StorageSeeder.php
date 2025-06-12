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
            // name, type, capacity_gb, interface, price, brand
            ['Samsung 970 EVO Plus', 'SSD', 1000, 'NVMe', 99.99, 'Samsung'],
            ['Crucial MX500', 'SSD', 1000, 'SATA', 59.99, 'Crucial'],
            ['Seagate Barracuda', 'HDD', 2000, 'SATA', 54.99, 'Seagate'],
            ['Western Digital Blue', 'SSD', 500, 'SATA', 42.99, 'Western Digital'],
            ['Kingston A2000', 'SSD', 500, 'NVMe', 34.99, 'Kingston'],
            ['Toshiba P300', 'HDD', 1000, 'SATA', 37.99, 'Toshiba'],
            ['Corsair MP600', 'SSD', 2000, 'NVMe', 159.99, 'Corsair'],
            ['ADATA SU800', 'SSD', 512, 'SATA', 31.99, 'ADATA'],
            ['Seagate FireCuda 520', 'SSD', 2000, 'NVMe', 249.99, 'Seagate'],
            ['Samsung 870 QVO', 'SSD', 2000, 'SATA', 112.99, 'Samsung'],
        ];

        foreach ($storages as [$name, $type, $capacity_gb, $interface, $price, $brandName]) {
            $brandId = DB::table('brands')->where('name', 'like', "%$brandName%")->value('id') ?? 1;

            $componentId = DB::table('components')->insertGetId([
                'name' => $name,
                'component_type_id' => $typeId,
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'price' => $price,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('storages')->insert([
                'component_id' => $componentId,
                'type'         => $type,
                'capacity_gb'  => $capacity_gb,
                'interface'    => $interface,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }
}
