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
        $builds = Build::with(['components.brand', 'components.images'])
            ->select('id', 'name', 'description', 'price')
            ->get()
            ->map(function ($build) {
                return [
                    'id'          => $build->id,
                    'name'        => $build->name,
                    'description' => $build->description,
                    'price'       => $build->price,
                    // On peut afficher la première image dispo, sinon un placeholder
                    'img_url'     => optional($build->components->first()?->images->first())->url 
                                      ?? '/images/default.png',
                ];
            });

        return Inertia::render('Builds/Index', [
            'builds' => $builds,
        ]);
    }

    /**
     * Affiche la page de création de build (formulaire).
     */
    public function create()
    {
        return Inertia::render('Builds/Create');
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
