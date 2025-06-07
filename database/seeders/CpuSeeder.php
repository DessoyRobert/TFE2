<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CpuSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $typeId = DB::table('component_types')->where('name', 'cpu')->value('id');
        $categoryId = DB::table('categories')->where('name', 'like', '%cpu%')->value('id');

        $cpus = [
            ['Ryzen 5 5600X', 'AM4', 6, 12, 3.7, 4.6, 65, null, 'AMD'],
            ['Ryzen 7 5800X', 'AM4', 8, 16, 3.8, 4.7, 105, null, 'AMD'],
            ['Ryzen 9 5900X', 'AM4', 12, 24, 3.7, 4.8, 105, null, 'AMD'],
            ['Ryzen 5 7600X', 'AM5', 6, 12, 4.7, 5.3, 105, 'Radeon Graphics', 'AMD'],
            ['Core i5-12400F', 'LGA1700', 6, 12, 2.5, 4.4, 65, null, 'Intel'],
            ['Core i7-12700K', 'LGA1700', 12, 20, 3.6, 5.0, 125, 'Intel UHD 770', 'Intel'],
            ['Core i9-12900K', 'LGA1700', 16, 24, 3.2, 5.2, 125, 'Intel UHD 770', 'Intel'],
            ['Core i5-13600KF', 'LGA1700', 14, 20, 3.5, 5.1, 125, null, 'Intel'],
            ['Ryzen 3 4100', 'AM4', 4, 8, 3.8, 4.0, 65, null, 'AMD'],
            ['Ryzen 7 7700X', 'AM5', 8, 16, 4.5, 5.4, 105, 'Radeon Graphics', 'AMD'],
        ];

        foreach ($cpus as [$name, $socket, $cores, $threads, $base, $boost, $tdp, $igpu, $brandName]) {
            $brandId = DB::table('brands')->where('name', 'like', "%$brandName%")->value('id') ?? 1;

            $componentId = DB::table('components')->insertGetId([
                'name' => $name,
                'component_type_id' => $typeId,
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('cpus')->insert([
                'component_id' => $componentId,
                'socket' => $socket,
                'core_count' => $cores,
                'thread_count' => $threads,
                'base_clock' => $base,
                'boost_clock' => $boost,
                'tdp' => $tdp,
                'integrated_graphics' => $igpu,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
