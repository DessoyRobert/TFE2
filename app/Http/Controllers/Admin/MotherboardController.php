<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Motherboard;
use App\Models\Component;
use App\Models\ComponentType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MotherboardController extends Controller
{
    
    // POST /admin/motherboards
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'brand_id'    => 'required|exists:brands,id',
            'price'       => 'nullable|numeric|min:0',
            'img_url'     => 'nullable|string',
            // Spécifiques Motherboard
            'socket'      => 'required|string|max:50',
            'chipset'     => 'required|string|max:100',
            'form_factor' => 'required|string|max:20',
            'ram_type'    => 'required|string|max:10',
            'max_ram'     => 'required|integer|min:1',
        ]);

        $componentTypeId = ComponentType::where('name', 'motherboard')->first()->id;

        $component = Component::create([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $componentTypeId,
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        $motherboard = Motherboard::create([
            'component_id' => $component->id,
            'socket'      => $validated['socket'],
            'chipset'     => $validated['chipset'],
            'form_factor' => $validated['form_factor'],
            'ram_type'    => $validated['ram_type'],
            'max_ram'     => $validated['max_ram'],
        ]);

        $motherboard->load('component.brand');

        return response()->json([
            'id' => $motherboard->id,
            'component_id' => $motherboard->component_id,
            'name' => $motherboard->component->name ?? '',
            'brand' => $motherboard->component->brand->name ?? '',
            'price' => $motherboard->component->price ?? '',
            'img_url' => $motherboard->component->img_url ?? '',
            'socket' => $motherboard->socket,
            'chipset' => $motherboard->chipset,
            'form_factor' => $motherboard->form_factor,
            'ram_type' => $motherboard->ram_type,
            'max_ram' => $motherboard->max_ram,
        ], Response::HTTP_CREATED);
    }

    // PUT/PATCH /admin/motherboards/{motherboard}
    public function update(Request $request, Motherboard $motherboard)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'brand_id'    => 'required|exists:brands,id',
            'price'       => 'nullable|numeric|min:0',
            'img_url'     => 'nullable|string',
            // Spécifiques Motherboard
            'socket'      => 'required|string|max:50',
            'chipset'     => 'required|string|max:100',
            'form_factor' => 'required|string|max:20',
            'ram_type'    => 'required|string|max:10',
            'max_ram'     => 'required|integer|min:1',
        ]);

        $motherboard->component->update([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        $motherboard->update([
            'socket'      => $validated['socket'],
            'chipset'     => $validated['chipset'],
            'form_factor' => $validated['form_factor'],
            'ram_type'    => $validated['ram_type'],
            'max_ram'     => $validated['max_ram'],
        ]);

        $motherboard->load('component.brand');

        return response()->json([
            'id' => $motherboard->id,
            'component_id' => $motherboard->component_id,
            'name' => $motherboard->component->name ?? '',
            'brand' => $motherboard->component->brand->name ?? '',
            'price' => $motherboard->component->price ?? '',
            'img_url' => $motherboard->component->img_url ?? '',
            'socket' => $motherboard->socket,
            'chipset' => $motherboard->chipset,
            'form_factor' => $motherboard->form_factor,
            'ram_type' => $motherboard->ram_type,
            'max_ram' => $motherboard->max_ram,
        ], Response::HTTP_OK);
    }

    // DELETE /admin/motherboards/{motherboard}
    public function destroy(Motherboard $motherboard)
    {
        $motherboard->component->delete();
        $motherboard->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
