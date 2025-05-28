<?php

namespace App\Http\Controllers;

use App\Models\Build;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BuildController extends Controller
{
    public function index()
    {
        $builds = Build::with('components')->get();

        return Inertia::render('Builds/Index', [
            'builds' => $builds
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
        'imgUrl' => 'nullable|string',
        'price' => 'nullable|numeric',
        'components' => 'required|array',
        'components.*.component_id' => 'required|integer|exists:components,id',
        'components.*.type' => 'required|string'
    ]);

    $build = Build::create([
        'name' => $data['name'],
        'description' => $data['description'] ?? '',
        'imgUrl' => $data['imgUrl'] ?? null,
        'price' => $data['price'] ?? null,
    ]);

    foreach ($data['components'] as $component) {
        $build->components()->attach($component['component_id'], [
            'type' => $component['type']
        ]);
    }

    return response()->json($build->load('components'), 201);
}


    public function show(Build $build)
    {
        $build->load('components');

        return Inertia::render('Builds/Show', [
            'build' => $build
        ]);
    }

    public function edit(Build $build)
    {
        $build->load('components');

        return Inertia::render('Builds/Edit', [
            'build' => $build
        ]);
    }

    public function update(Request $request, Build $build)
    {
        $rules = [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric',
            'img_url'     => 'nullable|string|max:255|url',
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

    public function destroy(Build $build)
    {
        $build->components()->detach();
        $build->delete();

        return redirect()->route('builds.index');
    }
}
