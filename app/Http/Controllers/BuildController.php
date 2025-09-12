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
    /** Image par défaut (Cloudinary) */
    protected string $fallbackImage = 'https://res.cloudinary.com/djllwl8c0/image/upload/v1753292540/Logo-JarvisTech-PNG-normalsansfond_pgxlrj.png';

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
     * Applique une transformation simple Cloudinary (f_auto,q_auto[,w_*])
     * Ne modifie pas les URLs non-Cloudinary.
     */
    protected function transformCdn(?string $url, ?int $w = null): ?string
    {
        if (!$url) return $this->fallbackImage;

        if (str_contains($url, '/upload/')) {
            $insertion = 'f_auto,q_auto';
            if ($w && $w > 0) {
                $insertion .= ',w_' . (int)$w;
            }
            return preg_replace('#/upload/#', '/upload/' . $insertion . '/', $url, 1);
        }

        return $url;
    }

    /**
     * Résout l'image principale d'un build, priorité:
     * 1) image liée au build
     * 2) première image du premier composant
     * 3) builds.img_url (legacy)
     * 4) fallback Cloudinary
     */
    protected function resolvePrimaryImage(Build $build, ?int $w = 640): string
    {
        $direct = optional($build->images->first())->url;
        if ($direct) return $this->transformCdn($direct, $w) ?? $direct;

        $compImg = optional($build->components->first()?->images->first())->url;
        if ($compImg) return $this->transformCdn($compImg, $w) ?? $compImg;

        if ($build->img_url) return $this->transformCdn($build->img_url, $w) ?? $build->img_url;

        return $this->fallbackImage;
    }

    /**
     * Index public des builds (+ builds de l'utilisateur).
     * Prix affiché = dynamique (live).
     * Ajoute "featured" (3 builds à la une) pour le carousel.
     * Recherche (q), tri étendu, per_page, withQueryString()
     */
    public function index(Request $request)
    {
        $user     = Auth::user();
        $sort     = $request->get('sort', 'newest');  // price_asc|price_desc|newest|oldest
        $q        = trim((string) $request->get('q', ''));
        $perPage  = max(12, (int) $request->get('per_page', 12));

        $query = Build::query()
            ->whereHas('components')
            ->where(function ($sub) use ($user) {
                $sub->where('is_public', true);
                if ($user) $sub->orWhere('user_id', $user->id);
            })
            ->select('builds.*')
            ->selectSub($this->liveTotalSubquery(), 'live_total')
            ->with([
                'images:id,imageable_id,imageable_type,url',
                'components' => function ($q) {
                    $q->select('components.id', 'components.name', 'components.price', 'components.brand_id', 'components.component_type_id');
                },
                'components.brand:id,name',
                'components.images:id,imageable_id,imageable_type,url',
                'components.type:id,name',
            ]);

        // Recherche portable (MySQL + PostgreSQL)
        if ($q !== '') {
            $needle = '%'.mb_strtolower($q).'%';
            $query->where(function ($qq) use ($needle, $q) {
                $qq->whereRaw('LOWER(builds.name) LIKE ?', [$needle])
                   ->orWhereRaw('LOWER(builds.description) LIKE ?', [$needle])
                   // Si Postgres, remplacer CHAR par TEXT si besoin
                   ->orWhereRaw('CAST(builds.id AS CHAR) LIKE ?', ['%'.$q.'%']);
            });
        }

        // Tri
        switch ($sort) {
            case 'price_asc':  $query->orderBy('live_total', 'asc');  break;
            case 'price_desc': $query->orderBy('live_total', 'desc'); break;
            case 'oldest':     $query->orderBy('builds.created_at', 'asc');  break;
            case 'newest':
            default:           $query->orderBy('builds.created_at', 'desc'); break;
        }

        $builds = $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (Build $build) {
                $primaryImage = $this->resolvePrimaryImage($build, 640);

                return [
                    'id'            => $build->id,
                    'name'          => $build->name,
                    'description'   => $build->description,
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
                                'url' => $this->transformCdn($img->url, 320) ?? $img->url,
                            ])->values() ?? [],
                        ];
                    })->values(),
                ];
            });

        // Builds "à la une" pour le carousel
        $featured = Build::query()
            ->where('is_public', true)
            ->where('is_featured', true)
            ->orderByRaw('COALESCE(featured_rank, 999) ASC')
            ->limit(3)
            ->with(['images:id,imageable_id,imageable_type,url', 'components.images'])
            ->select('builds.*')
            ->selectSub($this->liveTotalSubquery(), 'live_total')
            ->get()
            ->map(function (Build $b) {
                return [
                    'id'            => $b->id,
                    'name'          => $b->name,
                    'img_url'       => $this->resolvePrimaryImage($b, 960),
                    'display_total' => round((float) $b->live_total, 2),
                ];
            });

        return Inertia::render('Builds/Index', [
            'builds'   => $builds,
            'filters'  => [
                'q'        => $q,
                'sort'     => $sort,
                'per_page' => $perPage,
            ],
            'featured' => $featured,
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
                'img_url'         => $data['img_url'] ?? null, // legacy
                'price'           => $data['price'] ?? null,   // legacy
                'user_id'         => Auth::id(),
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

    /**
     * Affichage d'un build public (ou propriétaire/admin).
     * Le prix affiché est dynamique (composants actuels).
     */
    public function show(Build $build)
    {
        $user = Auth::user();

        $build = Build::query()
            ->whereKey($build->id)
            ->select('builds.*')
            ->selectSub($this->liveTotalSubquery(), 'live_total')
            ->with([
                'images:id,imageable_id,imageable_type,url',
                'components' => function ($q) {
                    $q->select('components.id', 'components.name', 'components.price', 'components.brand_id', 'components.component_type_id');
                },
                'components.brand:id,name',
                'components.images:id,imageable_id,imageable_type,url',
                'components.type:id,name',
            ])
            ->firstOrFail();

        $canView = $build->is_public
            || ($user && ($user->id === $build->user_id || ($user->is_admin ?? false)));
        if (!$canView) abort(404);

        $primaryImage = $this->resolvePrimaryImage($build, 960);

        $payload = [
            'id'            => $build->id,
            'name'          => $build->name,
            'description'   => $build->description,
            'img_url'       => $primaryImage,
            'display_total' => round((float) $build->live_total, 2),
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
                        'url' => $this->transformCdn($img->url, 480) ?? $img->url,
                    ])->values() ?? [],
                ];
            })->values(),
        ];

        return Inertia::render('Builds/Show', ['build' => $payload]);
    }
}
