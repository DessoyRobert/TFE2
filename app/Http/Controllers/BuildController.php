<?php

namespace App\Http\Controllers;

use App\Models\Build;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
class BuildController extends Controller
{
    /**
     * Liste tous les builds avec composants, marques et images.
     * Utilisé dans la page Builds/Index (vue liste).
     */
    public function index()
    {
        $builds = Build::with(['components.brand', 'components.images', 'components.type'])
            ->select('id', 'name', 'description', 'price', 'img_url')
            ->get()
            ->map(function ($build) {
                return [
                    'id'          => $build->id,
                    'name'        => $build->name,
                    'description' => $build->description,
                    'price'       => $build->price,
                    'img_url'     => $build->img_url
                                    ?? optional($build->components->first()?->images->first())->url
                                    ?? '/images/default.png',
                    'components'  => $build->components->map(function ($c) {
                        return [
                            'id'    => $c->id,
                            'name'  => $c->name,
                            'price' => $c->price,
                            'brand' => $c->brand?->only(['id','name']),
                            'component_type' => [
                                'slug' => $c->type->slug ?? null,
                                'name' => $c->type->name ?? null,
                            ],
                            'images' => $c->images?->map(fn($img) => [
                                'id' => $img->id,
                                'url' => $img->url,
                            ])->values() ?? [],
                        ];
                    })->values(),
                ];
            });
        return Inertia::render('Builds/Index', [
            'builds' => $builds,
        ]);
    }

    /**
     * Affiche la page de création de build (formulaire).
     * charge un build existant si "from" est en query string.
     */
    public function create(Request $request)
    {
        $prefill = null;

        if ($fromId = $request->integer('from')) {
            $build = Build::with(['components.componentType', 'components.brand'])->find($fromId);
            if ($build) {
                // On prépare un shape directement compatible avec le store
                $prefill = [
                    'meta' => [
                        'name'        => $build->name ? $build->name.' (copie)' : 'Build (copie)',
                        'description' => $build->description ?? '',
                        'img_url'     => $build->img_url ?? '',
                    ],
                    'items' => $build->components->map(function ($c) {
                        return [
                            'id'       => $c->id,
                            'name'     => $c->name,
                            'price'    => (float) $c->price,
                            'type_key' => strtolower(trim($c->componentType->slug ?? $c->componentType->name ?? '')),
                        ];
                    })->values(),
                ];
            }
        }

        return Inertia::render('Builds/Create', [
            'prefill' => $prefill, // Dans Create.vue: si présent, hydrate le store au mounted
        ]);
    }


    /**
     * Enregistre un nouveau build et associe les composants sélectionnés.
     * Retourne un JSON du build créé avec ses composants.
     */


public function store(Request $request)
{
    $data = $request->validate([
        'name'        => 'required|string',
        'description' => 'nullable|string',
        'img_url'     => 'nullable|string',
        'price'       => 'nullable|numeric',
        'components'  => 'required|array',
        'components.*.component_id' => 'required|integer|exists:components,id',
    ]);

    $build = Build::create([
        'name'        => $data['name'],
        'description' => $data['description'] ?? '',
        'img_url'     => $data['img_url'] ?? null,
        'price'       => $data['price'] ?? null,
        'user_id'     => auth()->id(),
        'build_code'  => strtoupper(Str::random(8)),
    ]);

    $componentIds = collect($data['components'])->pluck('component_id')->all();
    $build->components()->sync($componentIds);

    return redirect()->route('builds.index')
                     ->with('success', 'Build créé avec succès !');
}



    /**
     * Affiche les détails d’un build spécifique.
     */
    public function show(Build $build)
    {
        $build->load(['components.brand', 'components.images']);

        return Inertia::render('Builds/Show', [
            'build' => $build
        ]);
    }
}
