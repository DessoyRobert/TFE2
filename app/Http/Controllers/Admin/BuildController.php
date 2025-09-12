<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Build;
use App\Models\Component;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class BuildController extends Controller
{   
     /** Image par défaut (Cloudinary) */
        // GET /admin/builds
    public function index()
    {
        $builds = Build::with('components.brand')->paginate(15);
        return Inertia::render('Admin/Builds/Index', [
            'builds' => $builds
        ]);
    }

    // GET /admin/builds/{build}/edit
    public function edit(Build $build)
{
    // Charger quantités & snapshots prix dans le pivot + brand/type
    $build->load([
        'components' => fn($q) => $q
            ->select('components.id','components.name','components.price','components.brand_id','components.component_type_id')
            ->withPivot(['quantity','price_at_addition']),
        'components.brand:id,name',
        'components.type:id,name',
    ]);

    // Charger tous les composants (catalogue) avec brand/type (trié par nom)
    $allComponents = Component::with('brand:id,name', 'type:id,name')
        ->orderBy('name')
        ->paginate(15);

    return Inertia::render('Admin/Builds/Edit', [
        'build'         => $build,        // contient aussi is_featured / featured_rank
        'allComponents' => $allComponents
    ]);
}


    // PUT/PATCH /admin/builds/{build}
    public function update(Request $request, Build $build)
    {
        $rules = [
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'nullable|numeric',
            'img_url'       => 'nullable|string|max:255',
            'components'    => 'nullable|array',
            'is_featured'   => 'sometimes|boolean',
            'featured_rank' => 'nullable|integer|min:1|max:3',
        ];

        if ($request->filled('components')) {
            $rules['components.*.component_id'] = 'required|exists:components,id';
            $rules['components.*.quantity']     = 'required|integer|min:1';
        }

        $data = $request->validate($rules);

        $build->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'price'       => $data['price'] ?? null,
            'img_url'     => $data['img_url'] ?? null,
        ]);

        // Champs "à la une"
        $extra = [];
        if ($request->has('is_featured')) {
            $extra['is_featured'] = (bool) $request->boolean('is_featured');
        }
        if ($request->filled('featured_rank')) {
            $extra['featured_rank'] = (int) $request->input('featured_rank');
        }
        if ($extra) {
            $build->forceFill($extra)->save();
        }

        // Sync composants avec snapshot du prix à l’instant T
        if (!empty($data['components'])) {
            $rows = collect($data['components'])
                ->filter(fn($c) => !empty($c['component_id']))
                ->values();

            $ids    = $rows->pluck('component_id')->unique()->values();
            $prices = Component::whereIn('id', $ids)->pluck('price', 'id'); // évite le N+1

            $attach = $rows->mapWithKeys(function ($c) use ($prices) {
                $cid = (int) $c['component_id'];
                $qty = max(1, (int) ($c['quantity'] ?? 1));
                return [
                    $cid => [
                        'quantity'          => $qty,
                        'price_at_addition' => (float) ($prices[$cid] ?? 0),
                    ]
                ];
            })->toArray();

            $build->components()->sync($attach);
        } else {
            $build->components()->detach();
        }

        // Recalcul des totaux stockés (reporting)
        $build->recalculateTotals();

        return redirect()->route('admin.builds.index');
    }

    // DELETE /admin/builds/{build}
    public function destroy(Build $build)
    {
        $build->components()->detach();
        $build->delete();

        return redirect()->route('admin.builds.index');
    }

    /**
     * Basculer la visibilité (public/privé) d’un build.
     */
    public function toggleVisibility(Request $request, Build $build): JsonResponse|RedirectResponse
    {
        // Valide si présent, mais n’exige pas le champ
        $validated = $request->validate([
            'is_public' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('is_public', $validated)) {
            $build->is_public = (bool) $validated['is_public'];
        } else {
            // Mode "toggle" pur si aucune valeur envoyée
            $build->is_public = ! (bool) $build->is_public;
        }

        $build->save();

        if ($request->wantsJson()) {
            return response()->json([
                'ok'        => true,
                'build_id'  => $build->id,
                'is_public' => (bool) $build->is_public,
            ]);
        }

        return back()->with('success', 'Visibilité mise à jour.');
    }
}
