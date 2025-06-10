<?php

namespace App\Http\Controllers;

use App\Models\Build;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BuildController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $builds = Build::with('components.brand')
            ->when($search, fn($q) => $q->where('name', 'ILIKE', "%$search%"))
            ->get();

        return Inertia::render('Builds/Index', [
            'builds' => $builds,
            'isAdmin' => auth()->check() && auth()->user()->role === 'admin',
        ]);
    }


    public function create()
    {
        return Inertia::render('Builds/Create');
    }

    public function store(Request $request)
    {  
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'img_url' => 'nullable|string',
            'price' => 'nullable|numeric',
            'components' => 'required|array',
            'components.*.component_id' => 'required|integer|exists:components,id',
        ]);

        // On crée le build
        $build = Build::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'img_url' => $data['img_url'] ?? null,
            'price' => $data['price'] ?? null,
        ]);

        // On attache les composants (seulement l'id du component)
        foreach ($data['components'] as $component) {
            $build->components()->attach($component['component_id']);
        }

        return response()->json($build->load('components'), 201);
    }


        public function show(Build $build)
        {
            $build->load('components.brand');

            return Inertia::render('Builds/Show', [
                'build' => $build
            ]);
        }

        // GET /api/builds/{build}/edit
        public function edit(Build $build)
        {
            $build->load('components.brand');

            return Inertia::render('Builds/Edit', [
                'build' => $build
            ]);
        }

    // PUT/PATCH /api/builds/{build}
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

        return redirect()->route('builds.index');
    }

    // DELETE /api/builds/{build}
    public function destroy(Build $build)
    {
        $build->components()->detach();
        $build->delete();

        return redirect()->route('builds.index');
    }
}
