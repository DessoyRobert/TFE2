<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $typeId = DB::table('component_types')->where('name', 'case')->value('id');
        $categoryId = DB::table('categories')->where('name', 'like', '%case%')->value('id');

        $cases = [
            // name, form_factor, max_gpu_length, max_cooler_height, psu_form_factor, fan_mounts, price, brand
            ['NZXT H510', 'ATX', 381, 165, 'ATX', 4, 79.99, 'NZXT'],
            ['Fractal Design Meshify C', 'ATX', 315, 170, 'ATX', 6, 89.99, 'Fractal Design'],
            ['Corsair 4000D', 'ATX', 360, 170, 'ATX', 4, 84.99, 'Corsair'],
            ['be quiet! Pure Base 500', 'ATX', 369, 190, 'ATX', 5, 89.99, 'be quiet!'],
            ['Cooler Master NR200', 'Mini-ITX', 330, 155, 'SFX', 7, 99.99, 'Cooler Master'],
            ['Phanteks Eclipse P400A', 'ATX', 420, 160, 'ATX', 6, 84.99, 'Phanteks'],
            ['Lian Li PC-O11', 'ATX', 420, 155, 'ATX', 9, 129.99, 'Lian Li'],
            ['Thermaltake Core V21', 'Micro-ATX', 350, 185, 'ATX', 6, 54.99, 'Thermaltake'],
            ['Fractal Design Define 7', 'ATX', 491, 185, 'ATX', 7, 169.99, 'Fractal Design'],
            ['NZXT H210', 'Mini-ITX', 325, 165, 'SFX', 4, 89.99, 'NZXT'],

            // 20 boîtiers générés automatiquement
            ['Corsair Vault 752', 'ATX', 421, 155, 'ATX', 4, 134.29, 'Corsair'],
            ['Thermaltake Dawn 113', 'ATX', 460, 142, 'ATX', 6, 97.46, 'Thermaltake'],
            ['Fractal Design Shadow 819', 'ATX', 455, 152, 'ATX', 9, 126.14, 'Fractal Design'],
            ['Cooler Master Nova 267', 'ATX', 449, 169, 'ATX', 5, 123.77, 'Cooler Master'],
            ['NZXT Prime 329', 'Micro-ATX', 409, 166, 'ATX', 6, 101.13, 'NZXT'],
            ['be quiet! Stealth 266', 'Mini-ITX', 294, 149, 'SFX', 4, 111.86, 'be quiet!'],
            ['Phanteks Vision 206', 'Micro-ATX', 428, 142, 'ATX', 5, 121.96, 'Phanteks'],
            ['Lian Li Prime 528', 'Micro-ATX', 450, 171, 'ATX', 4, 124.65, 'Lian Li'],
            ['Fractal Design Strike 729', 'Mini-ITX', 466, 175, 'SFX', 4, 69.74, 'Fractal Design'],
            ['Corsair Prime 659', 'ATX', 402, 168, 'ATX', 4, 75.03, 'Corsair'],
            ['Cooler Master Vault 708', 'ATX', 363, 182, 'ATX', 6, 118.89, 'Cooler Master'],
            ['NZXT Stealth 195', 'Micro-ATX', 303, 144, 'ATX', 7, 160.06, 'NZXT'],
            ['Phanteks Edge 310', 'ATX', 479, 168, 'ATX', 3, 169.15, 'Phanteks'],
            ['Thermaltake Stealth 460', 'Micro-ATX', 392, 148, 'ATX', 7, 81.24, 'Thermaltake'],
            ['Lian Li Core 267', 'Mini-ITX', 301, 166, 'SFX', 4, 142.99, 'Lian Li'],
            ['Fractal Design Core 338', 'ATX', 493, 157, 'ATX', 3, 64.29, 'Fractal Design'],
            ['Corsair Nova 805', 'Mini-ITX', 489, 147, 'SFX', 5, 89.85, 'Corsair'],
            ['Cooler Master Dawn 676', 'Micro-ATX', 444, 185, 'ATX', 8, 139.02, 'Cooler Master'],
            ['be quiet! Prime 402', 'Mini-ITX', 343, 181, 'SFX', 4, 123.17, 'be quiet!'],
            ['NZXT Vault 347', 'ATX', 415, 190, 'ATX', 4, 63.94, 'NZXT'],
        ];

        foreach ($cases as [$name, $form_factor, $max_gpu_length, $max_cooler_height, $psu_form_factor, $fan_mounts, $price, $brandName]) {
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

            DB::table('case_models')->insert([
                'component_id'      => $componentId,
                'form_factor'       => $form_factor,
                'max_gpu_length'    => $max_gpu_length,
                'max_cooler_height' => $max_cooler_height,
                'psu_form_factor'   => $psu_form_factor,
                'fan_mounts'        => $fan_mounts,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }
    }
}
