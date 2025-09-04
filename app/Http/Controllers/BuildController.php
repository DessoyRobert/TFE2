<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Str;

class BuildController extends Controller
{
    public function index()
    {
        $builds = Build::query()
            ->whereHas('components')
            ->with([
                'components.brand:id,name',
                'components.images:id,imageable_id,imageable_type,url',
                'components.type:id,name',
            ])
            ->latest()
            ->paginate(12)
            ->through(function (Build $build) {
                $primaryImage = $build->img_url
                    ?? optional($build->components->first()?->images->first())->url
                    ?? '/images/default.png';

                return [
                    'id'            => $build->id,
                    'name'          => $build->name,
                    'description'   => $build->description,
                    'display_total' => $build->display_total,
                    'img_url'       => $primaryImage,
                    'components'    => $build->components->map(function ($c) {
                        return [
                            'id'    => $c->id,
                            'name'  => $c->name,
                            'price' => (float) $c->price,
                            'brand' => $c->brand?->only(['id','name']),
                            'component_type' => [
                                'slug' => Str::slug($c->type->name ?? ''), // calcule à la volée
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

        return Inertia::render('Builds/Index', ['builds' => $builds]);
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
                        $typeKey = Str::slug($c->type->name ?? ''); // calcule à la volée
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
                'price'           => $data['price'] ?? null,
                'user_id'         => auth()->id(),
                'build_code'      => strtoupper(Str::random(8)),
                'total_price'     => 0,
                'component_count' => 0,
            ]);

            foreach ($uniqueByType as $typeKey => $comp) {
                $build->components()->attach($comp->id, [
                    'quantity'          => 1,
                    'price_at_addition' => (float)($comp->price ?? 0),
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

            $build->recalculateTotals();

            return redirect()
                ->route('builds.index')
                ->with('success', 'Build créé avec succès !');
        });
    }

    public function show(Build $build)
    {
        $build->load(['components.brand', 'components.images', 'components.type']);

        return Inertia::render('Builds/Show', [
            'build' => $build,
        ]);
    }
}
