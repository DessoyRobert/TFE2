<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $categories = [
            // Nom, description éventuelle (si tu as une colonne description)
            ['CPU', 'Processeurs'],
            ['GPU', 'Cartes graphiques'],
            ['RAM', 'Mémoires vives'],
            ['Motherboard', 'Cartes mères'],
            ['Cooler', 'Systèmes de refroidissement'],
            ['PSU', 'Alimentations'],
            ['Storage SSD', 'Stockage SSD'],
            ['Storage HDD', 'Stockage HDD'],
            ['Case', 'Boîtiers'],
            ['Watercooling', 'Refroidissement liquide'],
            ['Aircooling', 'Refroidissement par air'],
            ['DDR4', 'Mémoire DDR4'],
            ['DDR5', 'Mémoire DDR5'],
            ['NVMe', 'Stockage NVMe'],
            ['SATA', 'Stockage SATA'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'name' => $category[0],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
