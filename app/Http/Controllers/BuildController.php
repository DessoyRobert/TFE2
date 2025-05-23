<?php

namespace App\Http\Controllers;

use App\Models\Build;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BuildController extends Controller
{
    public function index()
    {
        $builds = Build::with(['components'])->get();
        return Inertia::render('Builds/Index', ['builds' => $builds]);
    }

    public function create()
    {
        return Inertia::render('Builds/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric',
            // 'user_id'   => 'required|exists:users,id', // Active si besoin
            'components'  => 'nullable|array',
            'components.*.component_id' => 'required|exists:components,id',
            'components.*.quantity'     => 'required|integer|min:1'
        ]);

        $build = Build::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'price'       => $data['price'] ?? null,
            // 'user_id'   => $data['user_id'] ?? null,
        ]);

        // Ajoute les composants avec la quantité sur la table pivot
        if (!empty($data['components'])) {
            $syncData = [];
            foreach ($data['components'] as $comp) {
                $syncData[$comp['component_id']] = [
                    'quantity' => $comp['quantity']
                ];
            }
            $build->components()->attach($syncData);
        }

        return redirect()->route('builds.index');
    }

    public function show(Build $build)
    {
        $build->load(['components']);
        return Inertia::render('Builds/Show', ['build' => $build]);
    }

    public function edit(Build $build)
    {
        $build->load(['components']);
        return Inertia::render('Builds/Edit', ['build' => $build]);
    }

    public function update(Request $request, Build $build)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric',
            'components'  => 'nullable|array',
            'components.*.component_id' => 'required|exists:components,id',
            'components.*.quantity'     => 'required|integer|min:1'
        ]);

        $build->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'price'       => $data['price'] ?? null,
        ]);

        // Sync les composants et quantités
        if (!empty($data['components'])) {
            $syncData = [];
            foreach ($data['components'] as $comp) {
                $syncData[$comp['component_id']] = [
                    'quantity' => $comp['quantity']
                ];
            }
            $build->components()->sync($syncData);
        } else {
            // Si plus de composants sélectionnés, on vide
            $build->components()->detach();
        }

        return redirect()->route('builds.index');
    }

    public function destroy(Build $build)
    {
        $build->components()->detach();
        $build->delete();

        return redirect()->route('builds.index');
    }
}
