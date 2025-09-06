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
        $build->load('components.brand');
        // Charger tous les composants pour permettre la sélection/édition
        $allComponents = Component::with('brand', 'type')->paginate(15);

        return Inertia::render('Admin/Builds/Edit', [
            'build' => $build,
            'allComponents' => $allComponents
        ]);
    }

    // PUT/PATCH /admin/builds/{build}
    public function update(Request $request, Build $build)
    {
        $rules = [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric',
            'img_url'     => 'nullable|string|max:255',
            'components'  => 'nullable|array',
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

        if (!empty($data['components'])) {
            $build->components()->sync(
                collect($data['components'])->mapWithKeys(fn ($comp) => [
                    $comp['component_id'] => ['quantity' => $comp['quantity']]
                ])->toArray()
            );
        } else {
            $build->components()->detach();
        }

        return redirect()->route('admin.builds.index');
    }

    // DELETE /admin/builds/{build}
    public function destroy(Build $build)
    {
        $build->components()->detach();
        $build->delete();

        return redirect()->route('admin.builds.index');
    }
    // restreindre l’accès admin :
    /*
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->is_admin ?? false, 403);
            return $next($request);
        });
    }
    */

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
