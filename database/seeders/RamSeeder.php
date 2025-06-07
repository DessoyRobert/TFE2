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
            ['Corsair Vengeance LPX 16GB', 'DDR4', 16, 2, 3200, 'CL16', 'Corsair'],
            ['G.Skill Ripjaws V 32GB', 'DDR4', 32, 2, 3600, 'CL18', 'G.Skill'],
            ['Crucial Ballistix 16GB', 'DDR4', 16, 2, 3000, null, 'Crucial'],
            ['Kingston Fury Beast 32GB', 'DDR5', 32, 2, 5200, 'CL40', 'Kingston'],
            ['Team T-Force Delta RGB 16GB', 'DDR5', 16, 2, 6000, 'CL38', 'TeamGroup'],
            ['Corsair Dominator Platinum RGB 32GB', 'DDR5', 32, 2, 5600, 'CL36', 'Corsair'],
            ['Patriot Viper Steel 16GB', 'DDR4', 16, 2, 4400, 'CL19', 'Patriot'],
            ['ADATA XPG Gammix D30 16GB', 'DDR4', 16, 2, 3200, null, 'ADATA'],
            ['Kingston HyperX Fury 8GB', 'DDR4', 8, 1, 2400, 'CL15', 'Kingston'],
            ['G.Skill Trident Z RGB 32GB', 'DDR4', 32, 2, 3600, 'CL16', 'G.Skill'],
        ];

        foreach ($rams as [$name, $type, $capacity, $modules, $speed, $cas, $brandName]) {
            $brandId = DB::table('brands')->where('name', 'like', "%$brandName%")->value('id') ?? 1;

            $componentId = DB::table('components')->insertGetId([
                'name' => $name,
                'component_type_id' => $typeId,
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('rams')->insert([
                'component_id' => $componentId,
                'type' => $type,
                'capacity_gb' => $capacity,
                'modules' => $modules,
                'speed_mhz' => $speed,
                'cas_latency' => $cas,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
