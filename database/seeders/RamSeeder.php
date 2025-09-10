<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RamSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $typeId = DB::table('component_types')->where('name', 'ram')->value('id');
        $categoryId = DB::table('categories')->where('name', 'like', '%ram%')->value('id');

        $rams = [
            // name, type, capacity_gb, modules, speed_mhz, cas_latency, price, brand
            ['Corsair Vengeance LPX', 'DDR4', 16, 2, 3200, 16, 59.99, 'Corsair'],
            ['G.Skill Trident Z', 'DDR4', 32, 2, 3600, 18, 119.99, 'G.Skill'],
            ['Kingston Fury Beast', 'DDR4', 16, 2, 3000, 16, 49.99, 'Kingston'],
            ['Crucial Ballistix', 'DDR4', 32, 2, 3200, 16, 99.99, 'Crucial'],
            ['Corsair Dominator', 'DDR5', 32, 2, 5600, 36, 179.99, 'Corsair'],
            ['G.Skill Ripjaws V', 'DDR4', 16, 2, 3200, 16, 54.99, 'G.Skill'],
            ['Kingston Fury Renegade', 'DDR5', 32, 2, 6000, 32, 209.99, 'Kingston'],
            ['Patriot Viper Steel', 'DDR4', 16, 2, 3600, 17, 62.99, 'Patriot'],
            ['TeamGroup T-Force', 'DDR5', 32, 2, 5200, 40, 169.99, 'TeamGroup'],
            ['Crucial Pro', 'DDR4', 16, 2, 3200, 16, 47.99, 'Crucial'],
        ];

        foreach ($rams as [$name, $type, $capacity_gb, $modules, $speed_mhz, $cas_latency, $price, $brandName]) {
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

            DB::table('rams')->insert([
                'component_id' => $componentId,
                'type'         => $type,
                'capacity_gb'  => $capacity_gb,
                'modules'      => $modules,
                'speed_mhz'    => $speed_mhz,
                'cas_latency'  => $cas_latency,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }
}
