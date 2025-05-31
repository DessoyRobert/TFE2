<?php

namespace App\Http\Controllers;

use App\Models\Motherboard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MotherboardController extends Controller
{
    // GET /api/motherboards
    public function index()
    {
        return Motherboard::with('component.brand')->get()->map(function ($mb) {
            return [
                'id' => $mb->id,
                'component_id' => $mb->component_id,
                'name' => $mb->component->name ?? '',
                'brand' => $mb->component->brand->name ?? '',
                'price' => $mb->component->price ?? '',
                'img_url' => $mb->component->img_url ?? '',
                'socket' => $mb->socket,
                'chipset' => $mb->chipset,
                'form_factor' => $mb->form_factor,
                'ram_type' => $mb->ram_type,
                'max_ram' => $mb->max_ram,
            ];
        })->values();
    }

    // POST /api/motherboards
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Champs du composant principal
            'name'              => 'required|string|max:255',
            'brand_id'          => 'required|exists:brands,id',
            'component_type_id' => 'required|exists:component_types,id',
            'price'             => 'nullable|numeric|min:0',
            'img_url'           => 'nullable|string',
            // Champs spécifiques Motherboard
            'socket'      => 'required|string|max:50',
            'chipset'     => 'required|string|max:100',
            'form_factor' => 'required|string|max:20',
            'ram_type'    => 'required|string|max:10',
            'max_ram'     => 'required|integer|min:1',
        ]);

        // Création du Component principal
        $component = \App\Models\Component::create([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $validated['component_type_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        // Création de la Motherboard spécifique
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

    // GET /api/motherboards/{motherboard}
    public function show(Motherboard $motherboard)
    {
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

    // PUT/PATCH /api/motherboards/{motherboard}
    public function update(Request $request, Motherboard $motherboard)
    {
        $validated = $request->validate([
            // Champs du composant principal
            'name'              => 'required|string|max:255',
            'brand_id'          => 'required|exists:brands,id',
            'component_type_id' => 'required|exists:component_types,id',
            'price'             => 'nullable|numeric|min:0',
            'img_url'           => 'nullable|string',
            // Champs spécifiques Motherboard
            'socket'      => 'required|string|max:50',
            'chipset'     => 'required|string|max:100',
            'form_factor' => 'required|string|max:20',
            'ram_type'    => 'required|string|max:10',
            'max_ram'     => 'required|integer|min:1',
        ]);

        // Update du composant principal
        $motherboard->component->update([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $validated['component_type_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        // Update de la Motherboard
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

    // DELETE /api/motherboards/{motherboard}
    public function destroy(Motherboard $motherboard)
    {
        $motherboard->component->delete();
        $motherboard->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
