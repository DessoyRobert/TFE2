<?php

namespace App\Http\Controllers;

use App\Models\Ram;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RamController extends Controller
{
    // GET /api/rams
    public function index()
    {
        return Ram::with('component.brand')->get()->map(function ($ram) {
            return [
                'id' => $ram->id,
                'component_id' => $ram->component_id,
                'name' => $ram->component->name ?? '',
                'brand' => $ram->component->brand->name ?? '',
                'price' => $ram->component->price ?? '',
                'img_url' => $ram->component->img_url ?? '',
                'type' => $ram->type,
                'capacity_gb' => $ram->capacity_gb,
                'modules' => $ram->modules,
                'speed_mhz' => $ram->speed_mhz,
                'cas_latency' => $ram->cas_latency,
            ];
        })->values();
    }

    // POST /api/rams
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Champs du composant principal
            'name'              => 'required|string|max:255',
            'brand_id'          => 'required|exists:brands,id',
            'component_type_id' => 'required|exists:component_types,id',
            'price'             => 'nullable|numeric|min:0',
            'img_url'           => 'nullable|string',
            // Champs spécifiques RAM
            'type'         => 'required|string|max:50',
            'capacity_gb'  => 'required|integer|min:1',
            'modules'      => 'required|integer|min:1',
            'speed_mhz'    => 'required|integer|min:1',
            'cas_latency'  => 'nullable|integer|min:1',
        ]);

        // Création du Component principal
        $component = \App\Models\Component::create([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $validated['component_type_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        // Création de la RAM spécifique
        $ram = Ram::create([
            'component_id' => $component->id,
            'type'         => $validated['type'],
            'capacity_gb'  => $validated['capacity_gb'],
            'modules'      => $validated['modules'],
            'speed_mhz'    => $validated['speed_mhz'],
            'cas_latency'  => $validated['cas_latency'] ?? null,
        ]);

        $ram->load('component.brand');

        return response()->json([
            'id' => $ram->id,
            'component_id' => $ram->component_id,
            'name' => $ram->component->name ?? '',
            'brand' => $ram->component->brand->name ?? '',
            'price' => $ram->component->price ?? '',
            'img_url' => $ram->component->img_url ?? '',
            'type' => $ram->type,
            'capacity_gb' => $ram->capacity_gb,
            'modules' => $ram->modules,
            'speed_mhz' => $ram->speed_mhz,
            'cas_latency' => $ram->cas_latency,
        ], Response::HTTP_CREATED);
    }

    // GET /api/rams/{ram}
    public function show(Ram $ram)
    {
        $ram->load('component.brand');

        return response()->json([
            'id' => $ram->id,
            'component_id' => $ram->component_id,
            'name' => $ram->component->name ?? '',
            'brand' => $ram->component->brand->name ?? '',
            'price' => $ram->component->price ?? '',
            'img_url' => $ram->component->img_url ?? '',
            'type' => $ram->type,
            'capacity_gb' => $ram->capacity_gb,
            'modules' => $ram->modules,
            'speed_mhz' => $ram->speed_mhz,
            'cas_latency' => $ram->cas_latency,
        ], Response::HTTP_OK);
    }

    // PUT/PATCH /api/rams/{ram}
    public function update(Request $request, Ram $ram)
    {
        $validated = $request->validate([
            // Champs du composant principal
            'name'              => 'required|string|max:255',
            'brand_id'          => 'required|exists:brands,id',
            'component_type_id' => 'required|exists:component_types,id',
            'price'             => 'nullable|numeric|min:0',
            'img_url'           => 'nullable|string',
            // Champs spécifiques RAM
            'type'         => 'required|string|max:50',
            'capacity_gb'  => 'required|integer|min:1',
            'modules'      => 'required|integer|min:1',
            'speed_mhz'    => 'required|integer|min:1',
            'cas_latency'  => 'nullable|integer|min:1',
        ]);

        // Update du composant principal
        $ram->component->update([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $validated['component_type_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        // Update de la RAM
        $ram->update([
            'type'         => $validated['type'],
            'capacity_gb'  => $validated['capacity_gb'],
            'modules'      => $validated['modules'],
            'speed_mhz'    => $validated['speed_mhz'],
            'cas_latency'  => $validated['cas_latency'] ?? null,
        ]);

        $ram->load('component.brand');

        return response()->json([
            'id' => $ram->id,
            'component_id' => $ram->component_id,
            'name' => $ram->component->name ?? '',
            'brand' => $ram->component->brand->name ?? '',
            'price' => $ram->component->price ?? '',
            'img_url' => $ram->component->img_url ?? '',
            'type' => $ram->type,
            'capacity_gb' => $ram->capacity_gb,
            'modules' => $ram->modules,
            'speed_mhz' => $ram->speed_mhz,
            'cas_latency' => $ram->cas_latency,
        ], Response::HTTP_OK);
    }

    // DELETE /api/rams/{ram}
    public function destroy(Ram $ram)
    {
        $ram->component->delete();
        $ram->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
