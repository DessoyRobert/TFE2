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
            ['RTX 3060 Ti', '8GB GDDR6', 1410, 1665, 'NVIDIA'],
            ['RX 6700 XT', '12GB GDDR6', 2321, 2581, 'AMD'],
            ['RTX 3080', '10GB GDDR6X', 1440, 1710, 'NVIDIA'],
            ['RX 7900 XT', '20GB GDDR6', 2000, 2400, 'AMD'],
            ['RTX 4060 Ti', '8GB GDDR6', 2310, null, 'NVIDIA'],
            ['RX 6600', '8GB GDDR6', null, 2491, 'AMD'],
            ['RTX 4070', '12GB GDDR6X', 1920, 2475, 'NVIDIA'],
            ['RX 7800 XT', '16GB GDDR6', 2124, 2430, 'AMD'],
            ['RTX 4090', '24GB GDDR6X', 2235, 2520, 'NVIDIA'],
            ['RX 7600', '8GB GDDR6', 1720, 2655, 'AMD'],
        ];

        foreach ($gpus as [$chipset, $memory, $baseClock, $boostClock, $brandName]) {
            $brandId = DB::table('brands')->where('name', 'like', "%$brandName%")->value('id') ?? 1;

            $componentId = DB::table('components')->insertGetId([
                'name' => $chipset,
                'component_type_id' => $typeId,
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('gpus')->insert([
                'component_id' => $componentId,
                'chipset' => $chipset,
                'memory' => $memory,
                'base_clock' => $baseClock,
                'boost_clock' => $boostClock,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
