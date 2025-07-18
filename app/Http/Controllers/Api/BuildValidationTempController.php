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

        if (empty($componentIds)) {
            return response()->json([
                'errors' => ['Aucun composant sélectionné.'],
                'warnings' => []
            ]);
        }

        // Charger tous les composants et leur type
        $baseComponents = DB::table('components')
            ->whereIn('components.id', $componentIds)
            ->join('component_types', 'components.component_type_id', '=', 'component_types.id')
            ->select(
                'components.id',
                'components.name',
                'components.component_type_id',
                'component_types.name as type'
            )
            ->get();

        // Associer les données spécifiques à chaque composant
        $componentsData = [];
        foreach ($baseComponents as $component) {
            $type = strtolower($component->type);
            $table = match ($type) {
                'cpu' => 'cpus',
                'gpu' => 'gpus',
                'ram' => 'rams',
                'motherboard' => 'motherboards',
                'cooler' => 'coolers',
                'psu' => 'psus',
                'storage' => 'storages',
                'case' => 'case_models',
                default => null,
            };

            if ($table) {
                $specific = DB::table($table)->where('component_id', $component->id)->first();
                if ($specific) {
                    $componentsData[$component->component_type_id] = array_merge((array) $component, (array) $specific);
                }
            }
        }

        // Charger les règles de compatibilité
        $rules = DB::table('compatibility_rules')->get();

        $errors = [];
        $warnings = [];

        foreach ($rules as $rule) {
            $a = $componentsData[$rule->component_type_a_id] ?? null;
            $b = $componentsData[$rule->component_type_b_id] ?? null;

            if (!$a || !$b) {
                continue;
            }

            $valueA = $a[$rule->field_a] ?? null;
            $valueB = $b[$rule->field_b] ?? null;

            // Log facultatif en dev
            Log::debug('[Validation Compatibilité]', [
                'description' => $rule->description,
                'valueA' => $valueA,
                'valueB' => $valueB,
                'operator' => $rule->operator,
            ]);

            // Comparaison selon l'opérateur
            $compatible = match ($rule->operator) {
                '='     => $valueA == $valueB,
                '!='    => $valueA != $valueB,
                'LIKE'  => str_contains((string) $valueB, (string) $valueA), // ← inversion ici
                'IN'    => in_array((string) $valueB, explode(',', (string) $valueA)),
                '>='    => (float) $valueA >= (float) $valueB,
                '<='    => (float) $valueA <= (float) $valueB,
                '>'     => (float) $valueA > (float) $valueB,
                '<'     => (float) $valueA < (float) $valueB,
                default => false,
            };

            if (!$compatible) {
                if ($rule->rule_type === 'hard') {
                    $errors[] = $rule->description ?? "Incompatibilité détectée.";
                } else {
                    $warnings[] = $rule->description ?? "Avertissement de compatibilité.";
                }
            }
        }

        return response()->json([
            'errors' => $errors,
            'warnings' => $warnings,
        ]);
    }
}
