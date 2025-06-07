<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoolerSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $typeId = DB::table('component_types')->where('name', 'cooler')->value('id');
        $categoryId = DB::table('categories')->where('name', 'like', '%cooler%')->value('id');

        $coolers = [
            ['Noctua NH-D15', 'air', 2, 'AM4,LGA1200,LGA1700', 220, 165, 'Noctua'],
            ['Cooler Master Hyper 212 Black', 'air', 1, 'AM4,LGA1200,LGA1700', 150, 160, 'Cooler Master'],
            ['be quiet! Dark Rock Pro 4', 'air', 2, 'AM4,LGA1200,LGA1700', 250, 163, 'be quiet!'],
            ['NZXT Kraken X63', 'liquid', 2, 'AM4,LGA1200,LGA1700', 280, null, 'NZXT'],
            ['Corsair H100i RGB Platinum', 'liquid', 2, 'AM4,LGA1200,LGA1700', 240, null, 'Corsair'],
            ['Arctic Freezer 34 eSports DUO', 'air', 2, 'AM4,LGA1200,LGA1700', 210, 157, 'Arctic'],
            ['EK-AIO 240 D-RGB', 'liquid', 2, 'AM4,LGA1700', 250, null, 'EKWB'],
            ['Thermaltake UX200 ARGB', 'air', 1, 'AM4,LGA1200,LGA1700', 130, 153, 'Thermaltake'],
            ['Noctua NH-L9a-AM4', 'air', 1, 'AM4', 95, 37, 'Noctua'],
            ['Cooler Master MasterLiquid ML240L', 'liquid', 2, 'AM4,LGA1200,LGA1700', 200, null, 'Cooler Master'],
        ];

        foreach ($coolers as [$name, $type, $fanCount, $sockets, $tdp, $height, $brandName]) {
            $brandId = DB::table('brands')->where('name', 'like', "%$brandName%")->value('id') ?? 1;

            $componentId = DB::table('components')->insertGetId([
                'name' => $name,
                'component_type_id' => $typeId,
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('coolers')->insert([
                'component_id' => $componentId,
                'type' => $type,
                'fan_count' => $fanCount,
                'compatible_sockets' => $sockets,
                'max_tdp' => $tdp,
                'height_mm' => $height,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
