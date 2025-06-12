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
