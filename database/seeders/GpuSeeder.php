<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GpuSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $typeId = DB::table('component_types')->where('name', 'gpu')->value('id');
        $categoryId = DB::table('categories')->where('name', 'like', '%gpu%')->value('id');

        $gpus = [
            // name, chipset, memory, base_clock, boost_clock, length_mm, price, brand, recommended_wattage
            ['NVIDIA RTX 3060 Ti', 'RTX 3060 Ti', 8, 1410, 1665, 242, 369.99, 'NVIDIA', 600],
            ['NVIDIA RTX 3070', 'RTX 3070', 8, 1500, 1725, 242, 479.99, 'NVIDIA', 650],
            ['AMD RX 6700 XT', 'RX 6700 XT', 12, 2321, 2581, 267, 359.99, 'AMD', 650],
            ['AMD RX 6800', 'RX 6800', 16, 1700, 2105, 267, 499.00, 'AMD', 700],
            ['NVIDIA RTX 4090', 'RTX 4090', 24, 2235, 2520, 304, 1999.00, 'NVIDIA', 850],
            ['AMD RX 7900 XTX', 'RX 7900 XTX', 24, 1855, 2499, 287, 1199.00, 'AMD', 800],
            ['NVIDIA GTX 1660', 'GTX 1660', 6, 1530, 1785, 229, 179.00, 'NVIDIA', 450],
            ['AMD RX 6600', 'RX 6600', 8, 1626, 2491, 190, 229.00, 'AMD', 450],
            ['NVIDIA RTX 3080', 'RTX 3080', 10, 1440, 1710, 285, 799.00, 'NVIDIA', 750],
            ['AMD RX 5700 XT', 'RX 5700 XT', 8, 1605, 1905, 270, 249.99, 'AMD', 600],
        ];

        foreach ($gpus as [$name, $chipset, $memory, $base_clock, $boost_clock, $length_mm, $price, $brandName, $recommended_wattage]) {
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

            DB::table('gpus')->insert([
                'component_id'        => $componentId,
                'chipset'             => $chipset,
                'memory'              => $memory,
                'base_clock'          => $base_clock,
                'boost_clock'         => $boost_clock,
                'length_mm'           => $length_mm,
                'recommended_wattage' => $recommended_wattage,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }
    }
}
