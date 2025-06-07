<?php

namespace App\Services;

use App\Models\Build;
use App\Models\CompatibilityRule;

class BuildValidator
{
    protected Build $build;
    protected array $errors = [];
    protected array $warnings = [];

    public function __construct(Build $build)
    {
        $this->build = $build;
    }

    public function validate(): array
    {
        $rules = CompatibilityRule::all();

        foreach ($rules as $rule) {
            $componentA = $this->build->components
                ->where('component_type_id', $rule->component_type_a_id)
                ->first();
            $componentB = $this->build->components
                ->where('component_type_id', $rule->component_type_b_id)
                ->first();

            if (!$componentA || !$componentB) {
                continue; // une des deux pièces est absente du build
            }

            $valueA = $componentA->{$rule->field_a} ?? null;
            $valueB = $componentB->{$rule->field_b} ?? null;

            if (is_null($valueA) || is_null($valueB)) {
                continue; // champ manquant
            }

            $isValid = match ($rule->operator) {
                '='  => $valueA == $valueB,
                '!=' => $valueA != $valueB,
                '>'  => $valueA > $valueB,
                '>=' => $valueA >= $valueB,
                '<'  => $valueA < $valueB,
                '<=' => $valueA <= $valueB,
                default => true,
            };

            if (!$isValid) {
                if ($rule->rule_type === 'hard') {
                    $this->errors[] = $rule->description;
                } else {
                    $this->warnings[] = $rule->description;
                }
            }
        }

        return [
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }
}
