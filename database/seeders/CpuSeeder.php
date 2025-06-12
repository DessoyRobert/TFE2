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
            // name, socket, core_count, thread_count, base_clock, boost_clock, tdp, integrated_graphics, price, brand
            ['AMD Ryzen 7 5800X', 'AM4', 8, 16, 3.8, 4.7, 105, false, 329.99, 'AMD'],
            ['AMD Ryzen 5 5600G', 'AM4', 6, 12, 3.9, 4.4, 65, true, 189.90, 'AMD'],
            ['Intel Core i7-12700K', 'LGA1700', 12, 20, 3.6, 5.0, 125, true, 379.00, 'Intel'],
            ['Intel Core i5-12400F', 'LGA1700', 6, 12, 2.5, 4.4, 65, false, 179.00, 'Intel'],
            ['AMD Ryzen 9 7950X', 'AM5', 16, 32, 4.5, 5.7, 170, false, 699.00, 'AMD'],
            ['Intel Core i9-13900K', 'LGA1700', 24, 32, 3.0, 5.8, 125, true, 659.00, 'Intel'],
            ['AMD Ryzen 3 3200G', 'AM4', 4, 4, 3.6, 4.0, 65, true, 89.99, 'AMD'],
            ['Intel Core i3-12100', 'LGA1700', 4, 8, 3.3, 4.3, 60, true, 129.00, 'Intel'],
            ['AMD Ryzen 5 7600X', 'AM5', 6, 12, 4.7, 5.3, 105, false, 289.00, 'AMD'],
            ['Intel Core i5-13600K', 'LGA1700', 14, 20, 3.5, 5.1, 125, true, 339.00, 'Intel'],
        ];

        foreach ($cpus as [$name, $socket, $core_count, $thread_count, $base_clock, $boost_clock, $tdp, $integrated_graphics, $price, $brandName]) {
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

            DB::table('cpus')->insert([
                'component_id'         => $componentId,
                'socket'               => $socket,
                'core_count'           => $core_count,
                'thread_count'         => $thread_count,
                'base_clock'           => $base_clock,
                'boost_clock'          => $boost_clock,
                'tdp'                  => $tdp,
                'integrated_graphics'  => $integrated_graphics,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }
    }
}
