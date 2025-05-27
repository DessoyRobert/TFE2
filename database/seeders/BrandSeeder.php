<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run()
    {
        $brands = ['AMD', 'Intel', 'NVIDIA', 'Corsair', 'G.Skill', 'ASUS', 'MSI', 'Gigabyte', 'Seagate', 'Samsung', 'Kingston'];
        foreach ($brands as $brand) {
            Brand::create(['name' => $brand]);
        }
    }
}
