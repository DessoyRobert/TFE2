<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Build;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class BuildController extends Controller
{
    public function store(Request $request)
    {
        // 1) Validation stricte + messages parlants
        $data = $request->validate([
            'name'              => ['required','string','max:255'],
            'description'       => ['nullable','string'],
            'components'        => ['array'],           // [{component_id: int, quantity: int}]
            'components.*.component_id' => ['required','integer','exists:components,id'],
            'components.*.quantity'     => ['nullable','integer','min:1'],
            'is_public'         => ['nullable','boolean'],
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        try {
            $build = DB::transaction(function () use ($data, $userId) {
                // 2) Créer le build
                $build = Build::create([
                    'user_id'       => $userId,
                    'name'          => $data['name'],
                    'description'   => $data['description'] ?? null,
                    'is_public'     => (bool)($data['is_public'] ?? false),
                    // Valeurs par défaut “safe” si ta migration impose NOT NULL
                    'total_price'   => 0,
                    'component_count' => 0,
                ]);

                // 3) Attacher les composants (pivot)
                $total = 0;
                $count = 0;

                $componentsPayload = $data['components'] ?? [];
                if (!empty($componentsPayload)) {
                    $attach = [];
                    foreach ($componentsPayload as $item) {
                        $component = Component::find($item['component_id']);
                        if (!$component) {
                            // On journalise de façon explicite
                            throw new \RuntimeException("Component introuvable: {$item['component_id']}");
                        }
                        $qty = max(1, (int)($item['quantity'] ?? 1));
                        $attach[$component->id] = ['quantity' => $qty];

                        // Prix
                        $price = (float)($component->price ?? 0);
                        $total += $price * $qty;

                        $count += $qty;
                    }

                    // Sync sans détacher si tu gères update ailleurs (ici on crée → attach simple)
                    $build->components()->attach($attach);
                }

                // 4) Mettre à jour totaux
                $build->update([
                    'total_price'     => $total,
                    'component_count' => $count,
                ]);

                return $build->load(['components', 'components.brand', 'components.componentType']);
            });

            return response()->json([
                'message' => 'Build sauvegardé avec succès.',
                'build'   => $build,
            ], 201);
        } catch (\Throwable $e) {
            // 5) Journalise l’erreur complète pour debug serveur
            Log::error('[BuildController@store] Échec sauvegarde build', [
                'user_id' => $userId,
                'payload' => $data ?? null,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            // 6) Message précis pour le client
            return response()->json([
                'message' => 'Échec: impossible de sauvegarder le build.',
                'hint'    => $e->getMessage(),
            ], 422);
        }
    }

    public function index(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        $builds = Build::withCount('components')
            ->where('user_id', $userId)
            ->latest()
            ->paginate(12);

        return response()->json($builds);
    }
}
