<?php

namespace App\Http\Controllers;

use App\Models\Gpu;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GpuController extends Controller
{
    // GET /api/gpus
    public function index()
    {
        return Gpu::with('component.brand')->get()->map(function ($gpu) {
            return [
                'id' => $gpu->id,
                'component_id' => $gpu->component_id,
                'name' => $gpu->component->name ?? '',
                'brand' => $gpu->component->brand->name ?? '',
                'price' => $gpu->component->price ?? '',
                'img_url' => $gpu->component->img_url ?? '',
                'chipset' => $gpu->chipset,
                'memory' => $gpu->memory,
                'base_clock' => $gpu->base_clock ?? null,
                'boost_clock' => $gpu->boost_clock ?? null,
                'tdp' => $gpu->tdp ?? null,
            ];
        })->values();
    }

    // POST /api/gpus
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'brand_id'     => 'required|exists:brands,id',
            'price'        => 'nullable|numeric|min:0',
            'img_url'      => 'nullable|string',
            // Champs spécifiques GPU
            'chipset'      => 'required|string|max:255',
            'memory'       => 'required|string|max:20',
            'base_clock'   => 'nullable|numeric|min:0',
            'boost_clock'  => 'nullable|numeric|min:0',
            'tdp'          => 'nullable|integer|min:0',
        ]);

        // Sécurisation du type
        $componentTypeId = \App\Models\ComponentType::where('name', 'gpu')->first()->id;

        // Création du composant générique
        $component = \App\Models\Component::create([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $componentTypeId,
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        // Création du GPU spécifique
        $gpu = Gpu::create([
            'component_id' => $component->id,
            'chipset'      => $validated['chipset'],
            'memory'       => $validated['memory'],
            'base_clock'   => $validated['base_clock'] ?? null,
            'boost_clock'  => $validated['boost_clock'] ?? null,
            'tdp'          => $validated['tdp'] ?? null,
        ]);

        $gpu->load('component.brand');

        return response()->json([
            'id' => $gpu->id,
            'component_id' => $gpu->component_id,
            'name' => $gpu->component->name ?? '',
            'brand' => $gpu->component->brand->name ?? '',
            'price' => $gpu->component->price ?? '',
            'img_url' => $gpu->component->img_url ?? '',
            'chipset' => $gpu->chipset,
            'memory' => $gpu->memory,
            'base_clock' => $gpu->base_clock ?? null,
            'boost_clock' => $gpu->boost_clock ?? null,
            'tdp' => $gpu->tdp ?? null,
        ], Response::HTTP_CREATED);
    }

    // GET /api/gpus/{gpu}
    public function show(Gpu $gpu)
    {
        $gpu->load('component.brand');

        return response()->json([
            'id' => $gpu->id,
            'component_id' => $gpu->component_id,
            'name' => $gpu->component->name ?? '',
            'brand' => $gpu->component->brand->name ?? '',
            'price' => $gpu->component->price ?? '',
            'img_url' => $gpu->component->img_url ?? '',
            'chipset' => $gpu->chipset,
            'memory' => $gpu->memory,
            'base_clock' => $gpu->base_clock ?? null,
            'boost_clock' => $gpu->boost_clock ?? null,
            'tdp' => $gpu->tdp ?? null,
        ], Response::HTTP_OK);
    }

    // PUT/PATCH /api/gpus/{gpu}
    public function update(Request $request, Gpu $gpu)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'brand_id'     => 'required|exists:brands,id',
            'price'        => 'nullable|numeric|min:0',
            'img_url'      => 'nullable|string',
            // Champs spécifiques GPU
            'chipset'      => 'required|string|max:255',
            'memory'       => 'required|string|max:20',
            'base_clock'   => 'nullable|numeric|min:0',
            'boost_clock'  => 'nullable|numeric|min:0',
            'tdp'          => 'nullable|integer|min:0',
        ]);

        $gpu->component->update([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        $gpu->update([
            'chipset'      => $validated['chipset'],
            'memory'       => $validated['memory'],
            'base_clock'   => $validated['base_clock'] ?? null,
            'boost_clock'  => $validated['boost_clock'] ?? null,
            'tdp'          => $validated['tdp'] ?? null,
        ]);

        $gpu->load('component.brand');

        return response()->json([
            'id' => $gpu->id,
            'component_id' => $gpu->component_id,
            'name' => $gpu->component->name ?? '',
            'brand' => $gpu->component->brand->name ?? '',
            'price' => $gpu->component->price ?? '',
            'img_url' => $gpu->component->img_url ?? '',
            'chipset' => $gpu->chipset,
            'memory' => $gpu->memory,
            'base_clock' => $gpu->base_clock ?? null,
            'boost_clock' => $gpu->boost_clock ?? null,
            'tdp' => $gpu->tdp ?? null,
        ], Response::HTTP_OK);
    }

    // DELETE /api/gpus/{gpu}
    public function destroy(Gpu $gpu)
    {
        $gpu->component->delete();
        $gpu->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
