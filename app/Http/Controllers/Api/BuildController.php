<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Build;
use App\Models\Component;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; // lock pour idempotence
use Illuminate\Support\Str;

class BuildController extends Controller
{
    /**
     * Liste paginée des builds de l'utilisateur connecté.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $builds = Build::query()
            ->where('user_id', $userId)
            ->whereHas('components')
            ->with(['components' => function ($q) {
                $q->select('components.id', 'components.name', 'components.price', 'components.component_type_id');
            }])
            ->latest()
            ->paginate(10);

        return response()->json($builds);
    }

    /**
     * Crée un build à partir d'une liste de composants.
     *
     * Idempotence:
     *  - lock serveur sur user + hash(payload) pour éviter les doublons
     */
public function store(Request $request): JsonResponse
{
    $userId = (int) Auth::id();

    // 1) Normalisation du payload : accepte `component_ids` OU `components[]`
    $componentIds = $request->input('component_ids');
    if (!$componentIds) {
        $components = $request->input('components', []);
        $componentIds = collect($components)->map(function ($item) {
            if (is_array($item)) {
                // Priorité à component_id (plus explicite)
                return $item['component_id'] ?? $item['id'] ?? null;
            }
            return $item;
        });
    } else {
        $componentIds = collect($componentIds);
    }

    $componentIds = $componentIds
        ->map(fn ($v) => is_numeric($v) ? (int) $v : null)
        ->filter()
        ->unique()
        ->values();

    if ($componentIds->isEmpty()) {
        return response()->json(['ok' => false, 'message' => 'Aucun composant fourni.'], 422);
    }

    // 2) Idempotence : lock par (user + hash(payload))
    $rawKey  = json_encode([$userId, $componentIds->all()], JSON_UNESCAPED_UNICODE);
    $hash    = substr(sha1($rawKey), 0, 20);
    $lockKey = "build:create:user:{$userId}:{$hash}";

    try {
        return Cache::lock($lockKey, 10)->block(5, function () use ($request, $componentIds, $userId) {

            return DB::transaction(function () use ($request, $componentIds, $userId) {
                $multiTypeKeys = ['storage', 'stockage']; // type "multi" (quantité cumulée)

                // 3) Pré-charge des composants (pour dédup et prix)
                $all = Component::with('type:id,name')
                    ->whereIn('id', $componentIds)
                    ->get(['id', 'price', 'component_type_id']);

                if ($all->isEmpty()) {
                    return response()->json(['ok' => false, 'message' => 'Composants invalides.'], 422);
                }

                $uniqueByType  = [];   // typeKey => Component (CPU/GPU/RAM...)
                $storageCounts = [];   // componentId => qty (pour "storage")

                foreach ($componentIds as $id) {
                    /** @var \App\Models\Component|null $comp */
                    $comp = $all->firstWhere('id', $id);
                    if (!$comp) continue;

                    $typeName = (string) ($comp->type->name ?? '');
                    $typeKey  = Str::slug($typeName);

                    if (in_array($typeKey, $multiTypeKeys, true)) {
                        // Ex: deux SSD identiques → qty cumulée
                        $storageCounts[$comp->id] = ($storageCounts[$comp->id] ?? 0) + 1;
                    } else {
                        // Par défaut : un seul par type (le dernier gagne)
                        $uniqueByType[$typeKey] = $comp;
                    }
                }

                // 4) Génère un code build unique (réutilisé partout)
                $buildCode = Str::upper(Str::random(8));
                while (Build::where('build_code', $buildCode)->exists()) {
                    $buildCode = Str::upper(Str::random(8));
                }

                // 5) Création du build (totaux mis à 0, puis recalculés)
                $build = Build::create([
                    'user_id'         => $userId,
                    'name'            => $request->string('name')->toString() ?: 'Mon build',
                    'description'     => $request->string('description')->toString() ?: null,
                    'img_url'         => $request->string('img_url')->toString() ?: null,
                    'build_code'      => $buildCode,   // ne pas regénérer ici
                    'total_price'     => 0,
                    'component_count' => 0,
                ]);

                // 6) Attachements : uniques (qty=1)
                foreach ($uniqueByType as $comp) {
                    $build->components()->attach($comp->id, [
                        'quantity'          => 1,
                        'price_at_addition' => (float) ($comp->price ?? 0),
                    ]);
                }

                // 7) Attachements : stockage (qty cumulée)
                foreach ($storageCounts as $compId => $qty) {
                    $comp = $all->firstWhere('id', $compId);
                    if (!$comp) continue;

                    $build->components()->attach($comp->id, [
                        'quantity'          => max(1, (int) $qty),
                        'price_at_addition' => (float) ($comp->price ?? 0),
                    ]);
                }

                // 8) Recalcul/persistance des totaux (méthode modèle)
                $build->recalculateTotals();

                // 9) Réponse : renvoie l’URL de checkout avec le build (clé)
                return response()
                    ->json([
                        'ok'           => true,
                        'build_id'     => $build->id,
                        'total'        => $build->display_total,
                        // ⬇️ IMPORTANT : la page Checkout saura quoi commander même si le panier est vide
                        'redirect_url' => route('checkout.index', ['build' => $build->id]),
                    ], 201)
                    ->header('X-Idempotency-Lock', $buildCode);
            });
        });
    } catch (\Throwable $e) {
        Log::error('build_store_failed', [
            'user_id' => $userId,
            'payload' => $request->all(),
            'error'   => $e->getMessage(),
        ]);
        return response()->json(['ok' => false, 'message' => 'Impossible de sauvegarder le build.'], 500);
    }
}


    /**
     * Affiche un build (JSON).
     */
    public function show(Build $build): JsonResponse
    {
        $this->authorizeForUser(Auth::user(), 'view', $build);

        $build->load(['components' => function ($q) {
            $q->select('components.id', 'components.name', 'components.price', 'components.component_type_id');
        }]);

        return response()->json($build);
    }

    /**
     * Met à jour un build (remplacement total de la sélection si component_ids fourni).
     */
    public function update(Request $request, Build $build): JsonResponse
    {
        $this->authorizeForUser(Auth::user(), 'update', $build);

        // Normalise éventuels component_ids
        $componentIds = $request->input('component_ids');
        if (!$componentIds && $request->has('components')) {
            $componentIds = collect($request->input('components', []))->map(function ($item) {
                if (is_array($item)) {
                    return $item['component_id'] ?? $item['id'] ?? null;
                }
                return $item;
            });
        } else {
            $componentIds = collect($componentIds ?? []);
        }

        $componentIds = $componentIds
            ->map(fn ($v) => is_numeric($v) ? (int) $v : null)
            ->filter()
            ->unique()
            ->values();

        try {
            return DB::transaction(function () use ($request, $build, $componentIds) {
                $build->fill([
                    'name'        => $request->string('name')->toString() ?: $build->name,
                    'description' => $request->string('description')->toString() ?: $build->description,
                    'img_url'     => $request->string('img_url')->toString() ?: $build->img_url,
                ])->save();

                if ($componentIds->isNotEmpty()) {
                    $multiTypeKeys = ['storage', 'stockage'];

                    $all = Component::with('type:id,name')
                        ->whereIn('id', $componentIds)
                        ->get(['id', 'price', 'component_type_id']);

                    $uniqueByType  = [];
                    $storageCounts = [];

                    foreach ($componentIds as $id) {
                        $comp = $all->firstWhere('id', $id);
                        if (!$comp) continue;

                        $typeKey = Str::slug((string) ($comp->type->name ?? ''));

                        if (in_array($typeKey, $multiTypeKeys, true)) {
                            $storageCounts[$comp->id] = ($storageCounts[$comp->id] ?? 0) + 1;
                        } else {
                            $uniqueByType[$typeKey] = $comp;
                        }
                    }

                    // Reset & re-attach
                    $build->components()->detach();

                    foreach ($uniqueByType as $comp) {
                        $build->components()->attach($comp->id, [
                            'quantity'          => 1,
                            'price_at_addition' => (float) ($comp->price ?? 0),
                        ]);
                    }

                    foreach ($storageCounts as $compId => $qty) {
                        $comp = $all->firstWhere('id', $compId);
                        if (!$comp) continue;

                        $build->components()->attach($comp->id, [
                            'quantity'          => max(1, (int) $qty),
                            'price_at_addition' => (float) ($comp->price ?? 0),
                        ]);
                    }
                }

                $build->recalculateTotals();

                return response()->json([
                    'ok'        => true,
                    'build_id'  => $build->id,
                    'total'     => $build->display_total,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('build_update_failed', [
                'user_id'  => Auth::id(),
                'build_id' => $build->id,
                'payload'  => $request->all(),
                'error'    => $e->getMessage(),
            ]);
            return response()->json(['ok' => false, 'message' => 'Impossible de mettre à jour le build.'], 500);
        }
    }

    /**
     * Supprime un build et ses liaisons.
     */
    public function destroy(Build $build): JsonResponse
    {
        $this->authorizeForUser(Auth::user(), 'delete', $build);

        return DB::transaction(function () use ($build) {
            $build->components()->detach();
            $build->delete();
            return response()->json(['ok' => true]);
        });
    }
}
