<?php

namespace App\Http\Controllers;

use App\Models\Cooler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CoolerController extends Controller
{
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
            ];
        })->values();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'brand_id'     => 'required|exists:brands,id',
            'price'        => 'nullable|numeric|min:0',
            'img_url'      => 'nullable|string',
            'type'         => 'required|string|max:100',
            'fan_count'    => 'required|integer|min:1',
        ]);

        // Sécurise le component_type_id côté back
        $componentTypeId = \App\Models\ComponentType::where('name', 'cooler')->first()->id;

        $component = \App\Models\Component::create([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $componentTypeId,
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        $cooler = Cooler::create([
            'component_id' => $component->id,
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
        ], Response::HTTP_CREATED);
    }

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

    public function update(Request $request, Cooler $cooler)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'brand_id'     => 'required|exists:brands,id',
            'price'        => 'nullable|numeric|min:0',
            'img_url'      => 'nullable|string',
            'type'         => 'required|string|max:100',
            'fan_count'    => 'required|integer|min:1',
        ]);

        $cooler->component->update([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

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

    public function destroy(Cooler $cooler)
    {
        $cooler->component->delete();
        $cooler->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
