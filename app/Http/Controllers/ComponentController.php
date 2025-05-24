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
        return Component::all();
        //$components = Component::with(['type', 'cpu', 'gpu', 'ram', 'motherboard', 'storage', 'psu', 'cooler', 'casemodel'])->get();
        //return response()->json($components);
    }

    // POST /api/components
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'brand' => 'required',
            'component_type_id' => 'required|exists:component_types,id',
            'price' => 'nullable|numeric',
            'img_url' => 'nullable|string',
            'description' => 'nullable|string',
            'release_year' => 'nullable|integer',
            'ean' => 'nullable|string'
        ]);
        $component = Component::create($data);

        // Chercher le nom du type via la relation
        $typeName = $component->type->name;

        // Optionnel : log ou traiter les specs spécifiques
        // (ajoute ici la logique pour les sous-tables comme cpu/gpu/ram si tu veux !)

        return response()->json($component, 201);
    }

    // GET /api/components/{id}
    public function show($id)
    {
        $component = Component::with(['type', 'cpu', 'gpu', 'ram', 'motherboard', 'storage', 'psu', 'cooler', 'casemodel'])->findOrFail($id);
        return response()->json($component);
    }

    // PUT/PATCH /api/components/{id}
    public function update(Request $request, $id)
    {
        $component = Component::findOrFail($id);
        $data = $request->validate([
            'name' => 'required',
            'brand' => 'required',
            'component_type_id' => 'required|exists:component_types,id',
            'price' => 'nullable|numeric',
            'img_url' => 'nullable|string',
            'description' => 'nullable|string',
            'release_year' => 'nullable|integer',
            'ean' => 'nullable|string'
        ]);
        $component->update($data);

        return response()->json($component);
    }

    // DELETE /api/components/{id}
    public function destroy($id)
    {
        $component = Component::findOrFail($id);
        $component->delete();
        return response()->json(null, 204);
    }
}
