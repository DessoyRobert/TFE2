<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Component;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    // GET /api/components
    public function index()
    {
        // On charge la brand (et type si tu veux)
        return Component::with(['brand', 'type'])->get()->map(function ($component) {
            return [
                'id' => $component->id,
                'name' => $component->name,
                'brand' => $component->brand->name ?? '',
                'type' => $component->type->name ?? '',
                'price' => $component->price,
                'img_url' => $component->img_url,
                'description' => $component->description,
                'release_year' => $component->release_year,
                'ean' => $component->ean,
                // Ajoute ici d'autres champs spécifiques si tu veux
            ];
        })->values();
    }

    // POST /api/components
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

    // GET /api/components/{id}
    public function show($id)
    {
        $component = Component::with(['brand', 'type', 'cpu', 'gpu', 'ram', 'motherboard', 'storage', 'psu', 'cooler', 'casemodel'])->findOrFail($id);
        return response()->json($component);
    }

    // PUT/PATCH /api/components/{id}
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

    // DELETE /api/components/{id}
    public function destroy($id)
    {
        $component = Component::findOrFail($id);
        $component->delete();
        return response()->json(null, 204);
    }
    public function create()
    {
        return Inertia::render('Components/Create');
    }
}
