<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Component;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ComponentController extends Controller
{
    
    public function index()
    {
        $components = Component::with(['brand', 'type', 'images'])
            ->paginate(15);

        return Inertia::render('Admin/Components/Index', [
            'components' => $components,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Components/Create', [
            'brands' => \App\Models\Brand::all(['id', 'name']),
            'component_types' => \App\Models\ComponentType::all(['id', 'name']),
            'categories' => \App\Models\Category::all(['id', 'name']),
        ]);
    }

    public function edit($id)
    {
        $component = Component::with(['brand', 'type', 'images'])->findOrFail($id);

        // Détection du type (ex: cpu, gpu...)
        $type = $component->type->name ?? null;
        $specific = null;

        if ($type && in_array($type, ['cpu', 'gpu', 'ram', 'motherboard', 'storage', 'psu', 'cooler', 'case_model'])) {
            $relation = $type;
            if ($component->$relation) {
                $specific = $component->$relation;
            }
        }

        return Inertia::render('Admin/Components/Edit', [
            'component' => $component,
            'specific' => $specific,
            'brands' => \App\Models\Brand::all(['id', 'name']),
            'component_types' => \App\Models\ComponentType::all(['id', 'name']),
            'categories' => \App\Models\Category::all(['id', 'name']),
        ]);
    }

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
            'ean' => 'nullable|string',
        ]);

        $component = Component::create($data);

        return response()->json($component->load(['brand', 'type']), 201);
    }

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
            'ean' => 'nullable|string',
        ]);

        $component->update($data);

        return response()->json($component->load(['brand', 'type']));
    }

    public function destroy($id)
    {
        $component = Component::findOrFail($id);
        $component->delete();

        return response()->json(null, 204);
    }
}
