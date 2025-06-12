<?php

namespace App\Http\Controllers;

use App\Models\Storage;

class StorageController extends Controller
{
    public function index()
    {
        return Storage::with('component.brand')->get()->map(function ($s) {
            return [
                'id' => $s->component_id,
                'component_id' => $s->component_id,
                'name' => $s->component->name ?? '',
                'brand' => $s->component->brand->name ?? '',
                'price' => $s->component->price ?? '',
                'img_url' => $s->component->img_url ?? '',
                'type' => $s->type,
                'capacity_gb' => $s->capacity_gb,
                'interface' => $s->interface ?? '',
            ];
        })->values();
    }

    public function show(Storage $storage)
    {
        $storage->load('component.brand');

        return response()->json([
            'id' => $storage->component_id,
            'component_id' => $storage->component_id,
            'name' => $storage->component->name ?? '',
            'brand' => $storage->component->brand->name ?? '',
            'price' => $storage->component->price ?? '',
            'img_url' => $storage->component->img_url ?? '',
            'type' => $storage->type,
            'capacity_gb' => $storage->capacity_gb,
            'interface' => $storage->interface ?? '',
        ]);
    }
}
