<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompatibilityRuleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rules = [
            // CPU ↔ Motherboard : socket
            [
                'component_type_a_id' => $this->getTypeId('cpu'),
                'component_type_b_id' => $this->getTypeId('motherboard'),
                'rule_type'           => 'hard',
                'field_a'             => 'socket',
                'field_b'             => 'socket',
                'operator'            => '=',
                'description'         => 'Le socket du CPU doit correspondre à celui de la carte mère.',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],

            // RAM ↔ Motherboard : type RAM
            [
                'component_type_a_id' => $this->getTypeId('ram'),
                'component_type_b_id' => $this->getTypeId('motherboard'),
                'rule_type'           => 'hard',
                'field_a'             => 'type',
                'field_b'             => 'ram_type',
                'operator'            => '=',
                'description'         => 'La RAM doit être du bon type (DDR4, DDR5...) pour la carte mère.',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],

            // CPU ↔ Cooler : compatibilité socket
            [
                'component_type_a_id' => $this->getTypeId('cpu'),
                'component_type_b_id' => $this->getTypeId('cooler'),
                'rule_type'           => 'hard',
                'field_a'             => 'socket',
                'field_b'             => 'compatible_sockets',
                'operator'            => 'LIKE',
                'description'         => 'Le ventirad doit être compatible avec le socket du CPU.',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],

            // Motherboard ↔ CaseModel : form factor
            [
                'component_type_a_id' => $this->getTypeId('motherboard'),
                'component_type_b_id' => $this->getTypeId('case_model'),
                'rule_type'           => 'hard',
                'field_a'             => 'form_factor',
                'field_b'             => 'supported_form_factors',
                'operator'            => 'LIKE',
                'description'         => 'Le format de la carte mère doit être supporté par le boîtier.',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],

            // Cooler ↔ CaseModel : hauteur ventirad vs max boîtier
            [
                'component_type_a_id' => $this->getTypeId('cooler'),
                'component_type_b_id' => $this->getTypeId('case_model'),
                'rule_type'           => 'hard',
                'field_a'             => 'height_mm',
                'field_b'             => 'max_cooler_height',
                'operator'            => '<=',
                'description'         => 'La hauteur du ventirad ne doit pas dépasser la hauteur max supportée par le boîtier.',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],

            // GPU ↔ CaseModel : longueur GPU vs boîtier
            [
                'component_type_a_id' => $this->getTypeId('gpu'),
                'component_type_b_id' => $this->getTypeId('case_model'),
                'rule_type'           => 'hard',
                'field_a'             => 'length_mm',
                'field_b'             => 'max_gpu_length',
                'operator'            => '<=',
                'description'         => 'La longueur de la carte graphique ne doit pas dépasser la limite du boîtier.',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],

            // PSU ↔ CaseModel : format alimentation
            [
                'component_type_a_id' => $this->getTypeId('psu'),
                'component_type_b_id' => $this->getTypeId('case_model'),
                'rule_type'           => 'hard',
                'field_a'             => 'form_factor',
                'field_b'             => 'psu_form_factor',
                'operator'            => '=',
                'description'         => "Le format d'alimentation doit être compatible avec le boîtier.",
                'created_at'          => $now,
                'updated_at'          => $now,
            ],

            // PSU ↔ GPU : puissance minimale requise
            [
                'component_type_a_id' => $this->getTypeId('psu'),
                'component_type_b_id' => $this->getTypeId('gpu'),
                'rule_type'           => 'soft',
                'field_a'             => 'wattage',
                'field_b'             => 'recommended_wattage',
                'operator'            => '>=',
                'description'         => "La puissance de l'alimentation peut être insuffisante pour la carte graphique.",
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
        ];

        DB::table('compatibility_rules')->insert($rules);
    }

    private function getTypeId(string $typeName): int
    {
        return DB::table('component_types')->where('name', $typeName)->value('id');
    }
}
