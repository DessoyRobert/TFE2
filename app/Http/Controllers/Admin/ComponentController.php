<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Component;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    // POST /admin/components
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'brand_id' => 'required|exists:brands,id',
            'component_type_id' => 'required|exists:component_types,id',
            'price' => 'nullable|numeric',
            'img_url' => 'nullable|string',
            'description' => 'nullable|string',
            'release_year' => 'nullable|integer',
            'ean' => 'nullable|string'
        ]);

        $component = Component::create($data);

        return response()->json($component->load(['brand', 'type']), 201);
    }

    // PUT/PATCH /admin/components/{id}
    public function update(Request $request, $id)
    {
        $component = Component::findOrFail($id);
        $data = $request->validate([
            'name' => 'required',
            'brand_id' => 'required|exists:brands,id',
            'component_type_id' => 'required|exists:component_types,id',
            'price' => 'nullable|numeric',
            'img_url' => 'nullable|string',
            'description' => 'nullable|string',
            'release_year' => 'nullable|integer',
            'ean' => 'nullable|string'
        ]);

        $component->update($data);

        return response()->json($component->load(['brand', 'type']));
    }

    // DELETE /admin/components/{id}
    public function destroy($id)
    {
        $component = Component::findOrFail($id);
        $component->delete();
        return response()->json(null, 204);
    }
}
