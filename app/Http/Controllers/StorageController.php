<?php

namespace App\Http\Controllers;

use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StorageController extends Controller
{
public function index()
    {
        return Storage::with('component')->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'component_id' => $s->component_id,
                'name' => $s->component->name,
                'price' => $s->component->price,
                'img_url' => $s->component->img_url,
                'type' => $s->type,
                'capacity_gb' => $s->capacity_gb,
            ];
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'brand_id'     => 'required|exists:brands,id',
            'type'         => 'required|string|max:50',
            'capacity_gb'  => 'required|integer|min:1',
            'interface'    => 'required|string|max:50',
            'price'        => 'required|numeric|min:0',
        ]);

        $storage = Storage::create($validated);

        return response()->json($storage, Response::HTTP_CREATED);
    }

    public function show(Storage $storage)
    {
        $storage->load('brand');

        return response()->json($storage, Response::HTTP_OK);
    }

    public function update(Request $request, Storage $storage)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'brand_id'     => 'required|exists:brands,id',
            'type'         => 'required|string|max:50',
            'capacity_gb'  => 'required|integer|min:1',
            'interface'    => 'required|string|max:50',
            'price'        => 'required|numeric|min:0',
        ]);

        $storage->update($validated);

        return response()->json($storage, Response::HTTP_OK);
    }

    public function destroy(Storage $storage)
    {
        $storage->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
