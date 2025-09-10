<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gpu;
use App\Models\Component;
use App\Models\ComponentType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GpuController extends Controller
{
    // POST /admin/gpus
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

        $componentTypeId = ComponentType::where('name', 'gpu')->first()->id;

        $component = Component::create([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $componentTypeId,
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

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

    // PUT/PATCH /admin/gpus/{gpu}
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

    // DELETE /admin/gpus/{gpu}
    public function destroy(Gpu $gpu)
    {
        $gpu->component->delete();
        $gpu->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
