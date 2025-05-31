<?php

namespace App\Http\Controllers;

use App\Models\Cooler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CoolerController extends Controller
{
    // GET /api/coolers
    public function index()
    {
        return Cooler::with('component.brand')->get()->map(function ($cooler) {
            return [
                'id' => $cooler->id,
                'component_id' => $cooler->component_id,
                'name' => $cooler->component->name ?? '',
                'brand' => $cooler->component->brand->name ?? '',
                'price' => $cooler->component->price ?? '',
                'img_url' => $cooler->component->img_url ?? '',
                'type' => $cooler->type,
                'fan_count' => $cooler->fan_count,
                // Ajoute ici d'autres specs si tu veux (tdp, hauteur, etc.)
            ];
        })->values();
    }

    // POST /api/coolers
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Champs du composant principal
            'name'         => 'required|string|max:255',
            'brand_id'     => 'required|exists:brands,id',
            'component_type_id' => 'required|exists:component_types,id',
            'price'        => 'nullable|numeric|min:0',
            'img_url'      => 'nullable|string',
            // Champs spécifiques Cooler
            'type'         => 'required|string|max:100',
            'fan_count'    => 'required|integer|min:1',
        ]);

        // Création du Component principal (à adapter si tu as une logique d'association automatique)
        $component = \App\Models\Component::create([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $validated['component_type_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        // Création du Cooler spécifique
        $cooler = Cooler::create([
            'component_id' => $component->id,
            'type'         => $validated['type'],
            'fan_count'    => $validated['fan_count'],
        ]);

        // On recharge la relation pour retourner l'objet complet
        $cooler->load('component.brand');

        return response()->json([
            'id' => $cooler->id,
            'component_id' => $cooler->component_id,
            'name' => $cooler->component->name ?? '',
            'brand' => $cooler->component->brand->name ?? '',
            'price' => $cooler->component->price ?? '',
            'img_url' => $cooler->component->img_url ?? '',
            'type' => $cooler->type,
            'fan_count' => $cooler->fan_count,
        ], Response::HTTP_CREATED);
    }

    // GET /api/coolers/{cooler}
    public function show(Cooler $cooler)
    {
        $cooler->load('component.brand');

        return response()->json([
            'id' => $cooler->id,
            'component_id' => $cooler->component_id,
            'name' => $cooler->component->name ?? '',
            'brand' => $cooler->component->brand->name ?? '',
            'price' => $cooler->component->price ?? '',
            'img_url' => $cooler->component->img_url ?? '',
            'type' => $cooler->type,
            'fan_count' => $cooler->fan_count,
        ], Response::HTTP_OK);
    }

    // PUT/PATCH /api/coolers/{cooler}
    public function update(Request $request, Cooler $cooler)
    {
        $validated = $request->validate([
            // Champs du composant principal
            'name'         => 'required|string|max:255',
            'brand_id'     => 'required|exists:brands,id',
            'component_type_id' => 'required|exists:component_types,id',
            'price'        => 'nullable|numeric|min:0',
            'img_url'      => 'nullable|string',
            // Champs spécifiques Cooler
            'type'         => 'required|string|max:100',
            'fan_count'    => 'required|integer|min:1',
        ]);

        // Update du composant principal
        $cooler->component->update([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $validated['component_type_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        // Update du Cooler
        $cooler->update([
            'type'         => $validated['type'],
            'fan_count'    => $validated['fan_count'],
        ]);

        $cooler->load('component.brand');

        return response()->json([
            'id' => $cooler->id,
            'component_id' => $cooler->component_id,
            'name' => $cooler->component->name ?? '',
            'brand' => $cooler->component->brand->name ?? '',
            'price' => $cooler->component->price ?? '',
            'img_url' => $cooler->component->img_url ?? '',
            'type' => $cooler->type,
            'fan_count' => $cooler->fan_count,
        ], Response::HTTP_OK);
    }

    // DELETE /api/coolers/{cooler}
    public function destroy(Cooler $cooler)
    {
        // On supprime aussi le composant principal si besoin
        $cooler->component->delete();
        $cooler->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
