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
            ['NZXT H510', 'ATX', 381, 165, 'ATX', 4, 'NZXT'],
            ['Fractal Design Meshify C', 'ATX', 315, 172, 'ATX', 6, 'Fractal Design'],
            ['Cooler Master NR200', 'Mini-ITX', 330, 155, 'SFX', 5, 'Cooler Master'],
            ['Phanteks Eclipse P400A', 'ATX', 420, 160, 'ATX', 6, 'Phanteks'],
            ['Corsair 4000D Airflow', 'ATX', 360, 170, 'ATX', 6, 'Corsair'],
            ['Lian Li PC-O11 Dynamic', 'ATX', 420, 155, 'ATX', 9, 'Lian Li'],
            ['be quiet! Pure Base 500DX', 'ATX', 369, 190, 'ATX', 5, 'be quiet!'],
            ['Thermaltake Core V21', 'Micro-ATX', 350, 185, 'ATX', 6, 'Thermaltake'],
            ['InWin A1 Plus', 'Mini-ITX', 320, 160, 'SFX', 3, 'InWin'],
            ['SilverStone SG13', 'Mini-ITX', 270, 61, 'SFX', 2, 'SilverStone'],
        ];

        foreach ($cases as [$name, $formFactor, $gpuLength, $coolerHeight, $psuForm, $fanMounts, $brandName]) {
            $brandId = DB::table('brands')->where('name', 'like', "%$brandName%")->value('id') ?? 1;

            $componentId = DB::table('components')->insertGetId([
                'name' => $name,
                'component_type_id' => $typeId,
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('case_models')->insert([
                'component_id' => $componentId,
                'form_factor' => $formFactor,
                'max_gpu_length' => $gpuLength,
                'max_cooler_height' => $coolerHeight,
                'psu_form_factor' => $psuForm,
                'fan_mounts' => $fanMounts,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
