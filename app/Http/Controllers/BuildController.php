<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Str;

class BuildController extends Controller
{
    /**
     * Sous-requête SQL qui calcule le prix live d'un build:
     * SUM(components.price * build_component.quantity)
     */
    protected function liveTotalSubquery()
    {
        return DB::table('build_component')
            ->join('components', 'components.id', '=', 'build_component.component_id')
            ->selectRaw('COALESCE(SUM(COALESCE(components.price,0) * COALESCE(build_component.quantity,1)), 0)')
            ->whereColumn('build_component.build_id', 'builds.id');
    }

    /**
     * Index public des builds.
     * Affiche:
     *  - les builds publics
     *  - + les builds de l'utilisateur connecté (si authentifié)
     * Le prix affiché est TOUJOURS dynamique (prix actuels des composants).
     */
    public function index()
    {
        $user = Auth::user();
        $sort = request('sort', 'newest'); // price_asc|price_desc|newest

        $query = Build::query()
            ->whereHas('components')
            ->where(function ($sub) use ($user) {
                $sub->where('is_public', true);
                if ($user) {
                    $sub->orWhere('user_id', $user->id);
                }
            })
            // On ajoute la colonne virtuelle "live_total" calculée en SQL
            ->select('builds.*')
            ->selectSub($this->liveTotalSubquery(), 'live_total')
            // Eager-load léger pour l'affichage (image/brand/type)
            ->with([
                // On charge une partie des colonnes composant pour limiter la charge
                'components' => function ($q) {
                    $q->select('components.id', 'components.name', 'components.price', 'components.brand_id');
                },
                'components.brand:id,name',
                'components.images:id,imageable_id,imageable_type,url',
                'components.type:id,name',
            ]);

        // Tri optionnel (triable sur la colonne calculée live_total)
        if ($sort === 'price_asc') {
            $query->orderBy('live_total', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('live_total', 'desc');
        } else {
            $query->latest('builds.created_at');
        }

        $builds = $query->paginate(12)->through(function (Build $build) {
            $primaryImage = $build->img_url
                ?? optional($build->components->first()?->images->first())->url
                ?? '/images/default.png';

            return [
                'id'            => $build->id,
                'name'          => $build->name,
                'description'   => $build->description,
                // Prix dynamique depuis SQL (toujours actuel)
                'display_total' => round((float) $build->live_total, 2),
                'img_url'       => $primaryImage,
                'components'    => $build->components->map(function ($c) {
                    return [
                        'id'    => $c->id,
                        'name'  => $c->name,
                        'price' => (float) $c->price,
                        'brand' => $c->brand?->only(['id','name']),
                        'component_type' => [
                            'slug' => Str::slug($c->type->name ?? ''),
                            'name' => $c->type->name ?? null,
                        ],
                        'images' => $c->images?->map(fn($img) => [
                            'id'  => $img->id,
                            'url' => $img->url,
                        ])->values() ?? [],
                    ];
                })->values(),
            ];
        });

        return Inertia::render('Builds/Index', [
            'builds'  => $builds,
            'filters' => ['sort' => $sort],
        ]);
    }

    public function create(Request $request)
    {
        $prefill = null;

        if ($fromId = $request->integer('from')) {
            $build = Build::with(['components.type', 'components.brand'])->find($fromId);
            if ($build) {
                $prefill = [
                    'meta' => [
                        'name'        => $build->name ? $build->name.' (copie)' : 'Build (copie)',
                        'description' => $build->description ?? '',
                        'img_url'     => $build->img_url ?? '',
                    ],
                    'items' => $build->components->map(function ($c) {
                        $typeKey = Str::slug($c->type->name ?? '');
                        return [
                            'id'       => $c->id,
                            'name'     => $c->name,
                            'price'    => (float) $c->price,
                            'type_key' => $typeKey,
                        ];
                    })->values(),
                ];
            }
        }

        return Inertia::render('Builds/Create', [
            'prefill' => $prefill,
        ]);
    }

    /**
     * Store (form web)
     * $request['components'] = [{ component_id: number }, ...]
     * Répéter un storage => quantité cumulée.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'img_url'     => 'nullable|string|max:2048',
            'price'       => 'nullable|numeric',
            'components'  => 'required|array|min:1',
            'components.*.component_id' => 'required|integer|exists:components,id',
        ]);

        return DB::transaction(function () use ($data) {
            $uniqueTypeKeys = ['cpu','gpu','ram','motherboard','psu','case','cooler'];
            $multiTypeKeys  = ['storage'];

            $componentIds = collect($data['components'])->pluck('component_id')->all();

            $all = Component::with('type:id,name')
                ->whereIn('id', $componentIds)
                ->get(['id','price','component_type_id']);

            $uniqueByType  = [];
            $storageCounts = [];

            foreach ($componentIds as $id) {
                $comp = $all->firstWhere('id', $id);
                if (!$comp) continue;

                $typeKey = Str::slug($comp->type->name ?? '');

                if (in_array($typeKey, $uniqueTypeKeys, true)) {
                    $uniqueByType[$typeKey] = $comp;
                } elseif (in_array($typeKey, $multiTypeKeys, true)) {
                    $storageCounts[$comp->id] = ($storageCounts[$comp->id] ?? 0) + 1;
                }
            }

            $build = Build::create([
                'name'            => $data['name'],
                'description'     => $data['description'] ?? '',
                'img_url'         => $data['img_url'] ?? null,
                'price'           => $data['price'] ?? null, // legacy, non utilisé pour l'affichage
                'user_id'         => Auth::id(),
                'build_code'      => strtoupper(Str::random(8)),
                'total_price'     => 0,   // reporting interne éventuel
                'component_count' => 0,
            ]);

            foreach ($uniqueByType as $typeKey => $comp) {
                $build->components()->attach($comp->id, [
                    'quantity'          => 1,
                    'price_at_addition' => (float)($comp->price ?? 0), // snapshot utile pour l'historique
                ]);
            }

            foreach ($storageCounts as $compId => $qty) {
                $comp = $all->firstWhere('id', $compId);
                if (!$comp) continue;

                $build->components()->attach($comp->id, [
                    'quantity'          => max(1, (int)$qty),
                    'price_at_addition' => (float)($comp->price ?? 0),
                ]);
            }

            // Met à jour total_price/component_count (reporting), l'affichage reste dynamique
            $build->recalculateTotals();

            return redirect()
                ->route('builds.index')
                ->with('success', 'Build créé avec succès !');
        });
    }

    /**
     * Affichage d'un build public (ou propriétaire/admin).
     * Le prix affiché est dynamique (composants actuels).
     */
    public function show(Build $build)
    {
        $user = Auth::user();

        $canView = $build->is_public
            || ($user && ($user->id === $build->user_id || ($user->is_admin ?? false)));

        if (!$canView) {
            abort(404);
        }

        $build->load(['components.brand', 'components.images', 'components.type']);

        // Si ton modèle Build a l'accessor getDisplayTotalAttribute dynamique,
        // on peut simplement compter dessus. Sinon, calcule ici:
        $displayTotal = $build->components->reduce(function ($sum, $c) {
            $qty  = (int)($c->pivot->quantity ?? 1);
            $unit = (float)($c->price ?? 0); // prix ACTUEL
            return $sum + ($unit * $qty);
        }, 0.0);

        // On expose un attribut additionnel (utile si ton front lit build.display_total)
        $build->setAttribute('display_total', round($displayTotal, 2));

        return Inertia::render('Builds/Show', [
            'build' => $build,
        ]);
    }
}
