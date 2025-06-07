<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PsuSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('psus')->insert([
            [
                'component_id' => 1,
                'wattage' => 550,
                'certification' => '80 PLUS Bronze',
                'modular' => false,
                'form_factor' => 'ATX',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'component_id' => 2,
                'wattage' => 650,
                'certification' => '80 PLUS Gold',
                'modular' => true,
                'form_factor' => 'ATX',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'component_id' => 3,
                'wattage' => 750,
                'certification' => '80 PLUS Platinum',
                'modular' => true,
                'form_factor' => 'ATX',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'component_id' => 4,
                'wattage' => 450,
                'certification' => '80 PLUS Bronze',
                'modular' => false,
                'form_factor' => 'SFX',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'component_id' => 5,
                'wattage' => 600,
                'certification' => '80 PLUS Silver',
                'modular' => false,
                'form_factor' => 'ATX',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'component_id' => 6,
                'wattage' => 850,
                'certification' => '80 PLUS Gold',
                'modular' => true,
                'form_factor' => 'ATX',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'component_id' => 7,
                'wattage' => 1000,
                'certification' => '80 PLUS Platinum',
                'modular' => true,
                'form_factor' => 'ATX',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'component_id' => 8,
                'wattage' => 520,
                'certification' => '80 PLUS Bronze',
                'modular' => false,
                'form_factor' => 'SFX',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'component_id' => 9,
                'wattage' => 750,
                'certification' => '80 PLUS Gold',
                'modular' => true,
                'form_factor' => 'ATX',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'component_id' => 10,
                'wattage' => 650,
                'certification' => '80 PLUS Bronze',
                'modular' => false,
                'form_factor' => 'ATX',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
