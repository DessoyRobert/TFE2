<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Storage;
use App\Models\Component;
use App\Models\ComponentType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StorageController extends Controller
{
    // POST /admin/storages
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'brand_id'     => 'required|exists:brands,id',
            'price'        => 'nullable|numeric|min:0',
            'img_url'      => 'nullable|string',
            // Champs spécifiques Storage
            'type'         => 'required|string|max:50',
            'capacity_gb'  => 'required|integer|min:1',
            'interface'    => 'required|string|max:50',
        ]);

        $componentTypeId = ComponentType::where('name', 'storage')->first()->id;

        $component = Component::create([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $componentTypeId,
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

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

    // PUT/PATCH /admin/storages/{storage}
    public function update(Request $request, Storage $storage)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'brand_id'     => 'required|exists:brands,id',
            'price'        => 'nullable|numeric|min:0',
            'img_url'      => 'nullable|string',
            // Champs spécifiques Storage
            'type'         => 'required|string|max:50',
            'capacity_gb'  => 'required|integer|min:1',
            'interface'    => 'required|string|max:50',
        ]);

        $storage->component->update([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

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

    // DELETE /admin/storages/{storage}
    public function destroy(Storage $storage)
    {
        $storage->component->delete();
        $storage->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
