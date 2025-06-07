<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuildValidationTempController extends Controller
{
    /**
     * Ce contrôleur permet de valider dynamiquement un ensemble de composants sélectionnés,
     * sans avoir à les sauvegarder dans un build complet. C’est l'approche de PCPartPicker.
     */
    public function __invoke(Request $request)
    {
        // 1. On récupère la liste des IDs des composants sélectionnés côté front
        $componentIds = $request->input('component_ids', []);

        // 2. Si aucun composant n'est envoyé, on retourne une erreur immédiate
        if (empty($componentIds)) {
            return response()->json([
                'errors' => ['Aucun composant sélectionné.'],
                'warnings' => []
            ]);
        }

        // 3. On charge tous les composants et leur type (CPU, GPU...) en une requête
        $components = DB::table('components')
            ->whereIn('components.id', $componentIds)
            ->join('component_types', 'components.component_type_id', '=', 'component_types.id')
            ->select('components.id', 'components.name', 'components.component_type_id', 'component_types.name as type')
            ->get();

        // 4. On regroupe les composants par type, ex: ['cpu' => [1], 'gpu' => [4, 5]]
        $byType = [];
        foreach ($components as $comp) {
            $byType[$comp->type][] = $comp->id;
        }

        // 5. On récupère toutes les règles de compatibilité en base
        $rules = DB::table('compatibility_rules')->get();
        $errors = [];
        $warnings = [];

        // 6. On évalue chaque règle une à une
        foreach ($rules as $rule) {
            // On retrouve le nom des types de composants A et B (ex: cpu et motherboard)
            $typeA = DB::table('component_types')->find($rule->component_type_a_id)->name;
            $typeB = DB::table('component_types')->find($rule->component_type_b_id)->name;

            // On ignore cette règle si un des types n’est pas sélectionné
            if (!isset($byType[$typeA]) || !isset($byType[$typeB])) continue;

            // On teste chaque combinaison possible entre composants de type A et B
            foreach ($byType[$typeA] as $aId) {
                foreach ($byType[$typeB] as $bId) {
                    // On va chercher les données spécifiques aux deux composants
                    $a = DB::table($typeA . 's')->where('component_id', $aId)->first();
                    $b = DB::table($typeB . 's')->where('component_id', $bId)->first();

                    if (!$a || !$b) continue;

                    // On récupère les champs à comparer définis dans la règle
                    $valA = $a->{$rule->field_a} ?? null;
                    $valB = $b->{$rule->field_b} ?? null;

                    // Si l’un des champs n’existe pas, on ignore
                    if (is_null($valA) || is_null($valB)) continue;

                    // 7. On applique l’opérateur défini (=, !=, <=...) à ces deux valeurs
                    $isValid = match ($rule->operator) {
                        '='  => $valA == $valB,
                        '!=' => $valA != $valB,
                        '<=' => $valA <= $valB,
                        '>=' => $valA >= $valB,
                        '<'  => $valA < $valB,
                        '>'  => $valA > $valB,
                        default => true,
                    };

                    // 8. On stocke le message dans la bonne liste selon la sévérité
                    if (!$isValid) {
                        $target = $rule->rule_type === 'hard' ? 'errors' : 'warnings';
                        ${$target}[] = $rule->description;
                    }
                }
            }
        }

        // 9. On renvoie le résultat final avec erreurs et avertissements uniques
        return response()->json([
            'errors' => array_values(array_unique($errors)),
            'warnings' => array_values(array_unique($warnings)),
        ]);
    }
}
