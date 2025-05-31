<?php

namespace App\Http\Controllers;

use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StorageController extends Controller
{
    // GET /api/storages
    public function index()
    {
        return Storage::with('component.brand')->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'component_id' => $s->component_id,
                'name' => $s->component->name ?? '',
                'brand' => $s->component->brand->name ?? '',
                'price' => $s->component->price ?? '',
                'img_url' => $s->component->img_url ?? '',
                'type' => $s->type,
                'capacity_gb' => $s->capacity_gb,
                'interface' => $s->interface ?? '',
                // Ajoute ici d'autres specs (nvme, sata3...) si tu veux !
            ];
        })->values();
    }

    // POST /api/storages
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Champs du composant principal
            'name'              => 'required|string|max:255',
            'brand_id'          => 'required|exists:brands,id',
            'component_type_id' => 'required|exists:component_types,id',
            'price'             => 'nullable|numeric|min:0',
            'img_url'           => 'nullable|string',
            // Champs spécifiques Storage
            'type'         => 'required|string|max:50',
            'capacity_gb'  => 'required|integer|min:1',
            'interface'    => 'required|string|max:50',
        ]);

        // Création du Component principal
        $component = \App\Models\Component::create([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $validated['component_type_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        // Création du Storage spécifique
        $storage = Storage::create([
            'component_id' => $component->id,
            'type'         => $validated['type'],
            'capacity_gb'  => $validated['capacity_gb'],
            'interface'    => $validated['interface'],
        ]);

        $storage->load('component.brand');

        return response()->json([
            'id' => $storage->id,
            'component_id' => $storage->component_id,
            'name' => $storage->component->name ?? '',
            'brand' => $storage->component->brand->name ?? '',
            'price' => $storage->component->price ?? '',
            'img_url' => $storage->component->img_url ?? '',
            'type' => $storage->type,
            'capacity_gb' => $storage->capacity_gb,
            'interface' => $storage->interface ?? '',
        ], Response::HTTP_CREATED);
    }

    // GET /api/storages/{storage}
    public function show(Storage $storage)
    {
        $storage->load('component.brand');

        return response()->json([
            'id' => $storage->id,
            'component_id' => $storage->component_id,
            'name' => $storage->component->name ?? '',
            'brand' => $storage->component->brand->name ?? '',
            'price' => $storage->component->price ?? '',
            'img_url' => $storage->component->img_url ?? '',
            'type' => $storage->type,
            'capacity_gb' => $storage->capacity_gb,
            'interface' => $storage->interface ?? '',
        ], Response::HTTP_OK);
    }

    // PUT/PATCH /api/storages/{storage}
    public function update(Request $request, Storage $storage)
    {
        $validated = $request->validate([
            // Champs du composant principal
            'name'              => 'required|string|max:255',
            'brand_id'          => 'required|exists:brands,id',
            'component_type_id' => 'required|exists:component_types,id',
            'price'             => 'nullable|numeric|min:0',
            'img_url'           => 'nullable|string',
            // Champs spécifiques Storage
            'type'         => 'required|string|max:50',
            'capacity_gb'  => 'required|integer|min:1',
            'interface'    => 'required|string|max:50',
        ]);

        // Update du composant principal
        $storage->component->update([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $validated['component_type_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        // Update du Storage
        $storage->update([
            'type'         => $validated['type'],
            'capacity_gb'  => $validated['capacity_gb'],
            'interface'    => $validated['interface'],
        ]);

        $storage->load('component.brand');

        return response()->json([
            'id' => $storage->id,
            'component_id' => $storage->component_id,
            'name' => $storage->component->name ?? '',
            'brand' => $storage->component->brand->name ?? '',
            'price' => $storage->component->price ?? '',
            'img_url' => $storage->component->img_url ?? '',
            'type' => $storage->type,
            'capacity_gb' => $storage->capacity_gb,
            'interface' => $storage->interface ?? '',
        ], Response::HTTP_OK);
    }

    // DELETE /api/storages/{storage}
    public function destroy(Storage $storage)
    {
        $storage->component->delete();
        $storage->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
