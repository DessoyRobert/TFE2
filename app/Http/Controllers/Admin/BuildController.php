<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Build;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BuildController extends Controller
{
    // GET /admin/builds/{build}/edit
    public function edit(Build $build)
    {
        $build->load('components.brand');

        return Inertia::render('Builds/Edit', [
            'build' => $build
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

        return redirect()->route('builds.index');
    }

    // DELETE /admin/builds/{build}
    public function destroy(Build $build)
    {
        $build->components()->detach();
        $build->delete();

        return redirect()->route('builds.index');
    }
}
