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
            // name, type, fan_count, compatible_sockets, max_tdp, height_mm, price, brand
            ['Noctua NH-D15', 'Air', 2, 'AM4,LGA1700', 220, 165, 99.99, 'Noctua'],
            ['be quiet! Pure Rock 2', 'Air', 1, 'AM4,LGA1200', 150, 155, 44.99, 'be quiet!'],
            ['Corsair H100i RGB', 'Water', 2, 'AM4,LGA1700', 250, 27, 119.90, 'Corsair'],
            ['Cooler Master Hyper 212', 'Air', 1, 'AM4,LGA1200', 150, 159, 34.99, 'Cooler Master'],
            ['Arctic Freezer 34', 'Air', 1, 'AM4,LGA1700', 150, 157, 39.99, 'Arctic'],
            ['NZXT Kraken X63', 'Water', 2, 'AM4,LGA1700', 280, 30, 159.00, 'NZXT'],
            ['Deepcool GAMMAXX 400', 'Air', 1, 'AM4,LGA1200', 130, 155, 24.99, 'Deepcool'],
            ['Noctua NH-U12S', 'Air', 1, 'AM4,LGA1700', 140, 158, 74.99, 'Noctua'],
            ['Corsair iCUE H150i', 'Water', 3, 'AM5,LGA1700', 320, 27, 189.00, 'Corsair'],
            ['be quiet! Dark Rock Pro 4', 'Air', 2, 'AM4,LGA1700', 250, 163, 89.00, 'be quiet!'],
        ];

        foreach ($coolers as [$name, $type, $fan_count, $compatible_sockets, $max_tdp, $height_mm, $price, $brandName]) {
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

            DB::table('coolers')->insert([
                'component_id'        => $componentId,
                'type'                => $type,
                'fan_count'           => $fan_count,
                'compatible_sockets'  => $compatible_sockets,
                'max_tdp'             => $max_tdp,
                'height_mm'           => $height_mm,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }
    }
}
