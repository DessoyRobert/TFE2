<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BuildValidationTempController extends Controller
{
    
    public function __invoke(Request $request)
    {
        $componentIds = $request->input('component_ids', []);

        // Si rien n'est sélectionné, on renvoie quand même la clé compatible_ids vide
        if (empty($componentIds)) {
            return response()->json([
                'errors' => ['Aucun composant sélectionné.'],
                'warnings' => [],
                'compatible_ids' => [],
            ]);
        }

        // === 1) Charger les composants sélectionnés + type + tables spécifiques ===
        $selectedBase = DB::table('components')
            ->whereIn('components.id', $componentIds)
            ->join('component_types', 'components.component_type_id', '=', 'component_types.id')
            ->select(
                'components.id',
                'components.component_type_id',
                'components.name',
                'component_types.name as type'
            )
            ->get();

        // Map: type_id => data (merge composant + table spécifique cpus/gpus/...)
        $selectedByTypeId = [];
        foreach ($selectedBase as $c) {
            $table = $this->specificTableForType($c->type);
            if (!$table) {
                continue;
            }
            $specific = DB::table($table)->where('component_id', $c->id)->first();
            if (!$specific) {
                continue;
            }
            $selectedByTypeId[$c->component_type_id] = array_merge((array) $c, (array) $specific);
        }

        // === 2) Charger toutes les rules ===
        $rules = DB::table('compatibility_rules')->get();

        // === 3) Valider "erreurs / warnings" entre composants déjà sélectionnés ===
        $errors = [];
        $warnings = [];

        foreach ($rules as $rule) {
            $a = $selectedByTypeId[$rule->component_type_a_id] ?? null;
            $b = $selectedByTypeId[$rule->component_type_b_id] ?? null;
            if (!$a || !$b) {
                continue; // il faut les 2 pièces pour tester la règle
            }

            $valueA = $a[$rule->field_a] ?? null;
            $valueB = $b[$rule->field_b] ?? null;

            Log::debug('[Validation Compatibilité]', [
                'rule' => $rule->description,
                'A' => ['type_id' => $rule->component_type_a_id, 'field' => $rule->field_a, 'value' => $valueA],
                'B' => ['type_id' => $rule->component_type_b_id, 'field' => $rule->field_b, 'value' => $valueB],
                'operator' => $rule->operator,
            ]);

            if (!$this->compare($valueA, $rule->operator, $valueB)) {
                if ($rule->rule_type === 'hard') {
                    $errors[] = $rule->description ?? "Incompatibilité détectée.";
                } else {
                    $warnings[] = $rule->description ?? "Avertissement de compatibilité.";
                }
            }
        }

        // === 4) Compatibilité proactive: pour chaque type NON sélectionné,
        //     lister les IDs compatibles par rapport à TOUT ce qui est déjà choisi. ===

        // On récupère la liste des types (id => nom)
        $types = DB::table('component_types')->pluck('name', 'id');

        $compatibleIds = []; // ex { 'motherboard' => [12,14], 'ram' => [22,23] }

        foreach ($types as $typeId => $typeName) {
            // Si ce type est déjà sélectionné, on ne propose pas de candidats pour lui.
            if (isset($selectedByTypeId[$typeId])) {
                continue;
            }

            $table = $this->specificTableForType($typeName);
            if (!$table) {
                continue;
            }

            // Tous les candidats de ce type (IDs)
            $candidates = DB::table('components')
                ->where('component_type_id', $typeId)
                ->pluck('id')
                ->toArray();

            $validForThisType = [];

            foreach ($candidates as $candidateId) {
                // Données complètes du candidat (component + table spécifique)
                $compRow = DB::table('components')->where('id', $candidateId)->first();
                $specific = DB::table($table)->where('component_id', $candidateId)->first();
                if (!$compRow || !$specific) {
                    continue;
                }
                $candidateData = array_merge((array) $compRow, (array) $specific);

                // Le candidat doit passer toutes les règles qui impliquent "ce type"
                // face à TOUT composant déjà sélectionné.
                $passesAll = true;

                foreach ($rules as $rule) {
                    // La règle concerne-t-elle ce type ?
                    if ($rule->component_type_a_id != $typeId && $rule->component_type_b_id != $typeId) {
                        continue;
                    }

                    // Déterminer l'autre côté de la règle (un type sélectionné ?)
                    $otherTypeId = ($rule->component_type_a_id == $typeId)
                        ? $rule->component_type_b_id
                        : $rule->component_type_a_id;

                    $other = $selectedByTypeId[$otherTypeId] ?? null;
                    if (!$other) {
                        // Si l'autre type n'est pas sélectionné, cette règle ne bloque pas ce candidat
                        continue;
                    }

                    if ($rule->component_type_a_id == $typeId) {
                        // Candidat est côté A
                        $valueA = $candidateData[$rule->field_a] ?? null;
                        $valueB = $other[$rule->field_b] ?? null;
                    } else {
                        // Candidat est côté B
                        $valueA = $other[$rule->field_a] ?? null;
                        $valueB = $candidateData[$rule->field_b] ?? null;
                    }

                    if (!$this->compare($valueA, $rule->operator, $valueB)) {
                        // Si la règle ne passe pas, le candidat est rejeté
                        $passesAll = false;
                        break;
                    }
                }

                if ($passesAll) {
                    $validForThisType[] = $candidateId;
                }
            }

            // Clé normalisée côté front ('case' => 'case_model', etc.)
            $slug = $this->normalizeTypeName($typeName);
            $compatibleIds[$slug] = $validForThisType;
        }

        return response()->json([
            'errors' => $errors,
            'warnings' => $warnings,
            'compatible_ids' => $compatibleIds,
        ]);
    }

    /**
     * Mappe un "type" (nom) vers la table spécifique correspondante.
     */
    private function specificTableForType(string $typeName): ?string
    {
        $t = strtolower(trim($typeName));
        return match ($t) {
            'cpu'         => 'cpus',
            'gpu'         => 'gpus',
            'ram'         => 'rams',
            'motherboard' => 'motherboards',
            'cooler'      => 'coolers',
            'psu'         => 'psus',
            'storage'     => 'storages',
            'case'        => 'case_models',
            default       => null,
        };
    }

    /**
     * Normalise le nom de type pour correspondre aux clés du store front.
     */
    private function normalizeTypeName(string $typeName): string
    {
        $k = strtolower(trim($typeName));
        return match ($k) {
            'cpu'                     => 'cpu',
            'gpu'                     => 'gpu',
            'ram', 'mémoire', 'memoire' => 'ram',
            'motherboard', 'carte mère', 'carte mere' => 'motherboard',
            'storage', 'ssd', 'hdd'   => 'storage',
            'psu', 'power supply', 'alimentation' => 'psu',
            'cooler', 'ventirad'      => 'cooler',
            'case', 'case model', 'boîtier', 'boitier' => 'case_model',
            default => $k,
        };
    }

    /**
     * Comparaison générique selon l'opérateur de la rule.
     */
    private function compare(mixed $a, string $op, mixed $b): bool
    {
        // Sécuriser les nulls
        if (is_null($a) || is_null($b)) {
            return false;
        }

        switch (strtoupper($op)) {
            case '=':   return $a == $b;
            case '!=':  return $a != $b;
            case 'LIKE':
                return str_contains((string) $b, (string) $a);
            case 'IN':
                $arr = array_map('trim', explode(',', (string) $a));
                return in_array((string) $b, $arr, true);
            case '>=':  return (float) $a >= (float) $b;
            case '<=':  return (float) $a <= (float) $b;
            case '>':   return (float) $a > (float) $b;
            case '<':   return (float) $a < (float) $b;
            default:    return false;
        }
    }
}
