<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompatibilityRuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('compatibility_rules')->insert([
            [
                'component_type_a_id' => $this->getTypeId('cpu'),
                'component_type_b_id' => $this->getTypeId('motherboard'),
                'rule_type'           => 'hard',
                'field_a'             => 'socket',
                'field_b'             => 'socket',
                'operator'            => '=',
                'description'         => 'Le socket du CPU doit correspondre à celui de la carte mère.',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'component_type_a_id' => $this->getTypeId('ram'),
                'component_type_b_id' => $this->getTypeId('motherboard'),
                'rule_type'           => 'hard',
                'field_a'             => 'type',
                'field_b'             => 'ram_type',
                'operator'            => '=',
                'description'         => 'La RAM doit être du bon type (DDR4, DDR5...) pour la carte mère.',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'component_type_a_id' => $this->getTypeId('gpu'),
                'component_type_b_id' => $this->getTypeId('psu'),
                'rule_type'           => 'soft',
                'field_a'             => 'recommended_wattage',
                'field_b'             => 'wattage',
                'operator'            => '<=',
                'description'         => 'La puissance de l’alim peut être insuffisante pour la carte graphique.',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
        ]);
    }

    private function getTypeId(string $typeName): int
    {
        return DB::table('component_types')->where('name', $typeName)->value('id');
    }
}
