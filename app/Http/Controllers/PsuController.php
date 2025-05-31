<?php

namespace App\Http\Controllers;

use App\Models\Psu;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PsuController extends Controller
{
    // GET /api/psus
    public function index()
    {
        return Psu::with('component.brand')->get()->map(function ($psu) {
            return [
                'id' => $psu->id,
                'component_id' => $psu->component_id,
                'name' => $psu->component->name ?? '',
                'brand' => $psu->component->brand->name ?? '',
                'price' => $psu->component->price ?? '',
                'img_url' => $psu->component->img_url ?? '',
                'wattage' => $psu->wattage,
                'certification' => $psu->certification ?? '',
                'modular' => $psu->modular,
            ];
        })->values();
    }

    // POST /api/psus
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Champs du composant principal
            'name'              => 'required|string|max:255',
            'brand_id'          => 'required|exists:brands,id',
            'component_type_id' => 'required|exists:component_types,id',
            'price'             => 'nullable|numeric|min:0',
            'img_url'           => 'nullable|string',
            // Champs spécifiques PSU
            'wattage'      => 'required|integer|min:100',
            'certification'=> 'required|string|max:50',
            'modular'      => 'required|boolean',
        ]);

        // Création du Component principal
        $component = \App\Models\Component::create([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $validated['component_type_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        // Création du PSU spécifique
        $psu = Psu::create([
            'component_id'  => $component->id,
            'wattage'       => $validated['wattage'],
            'certification' => $validated['certification'],
            'modular'       => $validated['modular'],
        ]);

        $psu->load('component.brand');

        return response()->json([
            'id' => $psu->id,
            'component_id' => $psu->component_id,
            'name' => $psu->component->name ?? '',
            'brand' => $psu->component->brand->name ?? '',
            'price' => $psu->component->price ?? '',
            'img_url' => $psu->component->img_url ?? '',
            'wattage' => $psu->wattage,
            'certification' => $psu->certification ?? '',
            'modular' => $psu->modular,
        ], Response::HTTP_CREATED);
    }

    // GET /api/psus/{psu}
    public function show(Psu $psu)
    {
        $psu->load('component.brand');

        return response()->json([
            'id' => $psu->id,
            'component_id' => $psu->component_id,
            'name' => $psu->component->name ?? '',
            'brand' => $psu->component->brand->name ?? '',
            'price' => $psu->component->price ?? '',
            'img_url' => $psu->component->img_url ?? '',
            'wattage' => $psu->wattage,
            'certification' => $psu->certification ?? '',
            'modular' => $psu->modular,
        ], Response::HTTP_OK);
    }

    // PUT/PATCH /api/psus/{psu}
    public function update(Request $request, Psu $psu)
    {
        $validated = $request->validate([
            // Champs du composant principal
            'name'              => 'required|string|max:255',
            'brand_id'          => 'required|exists:brands,id',
            'component_type_id' => 'required|exists:component_types,id',
            'price'             => 'nullable|numeric|min:0',
            'img_url'           => 'nullable|string',
            // Champs spécifiques PSU
            'wattage'      => 'required|integer|min:100',
            'certification'=> 'required|string|max:50',
            'modular'      => 'required|boolean',
        ]);

        // Update du composant principal
        $psu->component->update([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $validated['component_type_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        // Update du PSU
        $psu->update([
            'wattage'       => $validated['wattage'],
            'certification' => $validated['certification'],
            'modular'       => $validated['modular'],
        ]);

        $psu->load('component.brand');

        return response()->json([
            'id' => $psu->id,
            'component_id' => $psu->component_id,
            'name' => $psu->component->name ?? '',
            'brand' => $psu->component->brand->name ?? '',
            'price' => $psu->component->price ?? '',
            'img_url' => $psu->component->img_url ?? '',
            'wattage' => $psu->wattage,
            'certification' => $psu->certification ?? '',
            'modular' => $psu->modular,
        ], Response::HTTP_OK);
    }

    // DELETE /api/psus/{psu}
    public function destroy(Psu $psu)
    {
        $psu->component->delete();
        $psu->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
