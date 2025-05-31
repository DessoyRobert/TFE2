<?php

namespace App\Http\Controllers;

use App\Models\Cpu;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CpuController extends Controller
{
    // GET /api/cpus
    public function index()
    {
        return Cpu::with('component.brand')->get()->map(function ($cpu) {
            return [
                'id' => $cpu->id,
                'component_id' => $cpu->component_id,
                'name' => $cpu->component->name ?? '',
                'brand' => $cpu->component->brand->name ?? '',
                'price' => $cpu->component->price ?? '',
                'img_url' => $cpu->component->img_url ?? '',
                'socket' => $cpu->socket,
                'core_count' => $cpu->core_count,
                'thread_count' => $cpu->thread_count,
                'base_clock' => $cpu->base_clock,
                'boost_clock' => $cpu->boost_clock,
                'tdp' => $cpu->tdp,
            ];
        })->values();
    }

    // POST /api/cpus
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Champs du composant principal
            'name'              => 'required|string|max:255',
            'brand_id'          => 'required|exists:brands,id',
            'component_type_id' => 'required|exists:component_types,id',
            'price'             => 'nullable|numeric|min:0',
            'img_url'           => 'nullable|string',
            // Champs spécifiques CPU
            'socket'      => 'required|string|max:50',
            'core_count'  => 'required|integer|min:1',
            'thread_count'=> 'required|integer|min:1',
            'base_clock'  => 'required|numeric|min:0',
            'boost_clock' => 'nullable|numeric|min:0',
            'tdp'         => 'required|integer|min:0',
        ]);

        // Création du Component principal
        $component = \App\Models\Component::create([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $validated['component_type_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        // Création du CPU spécifique
        $cpu = Cpu::create([
            'component_id' => $component->id,
            'socket'       => $validated['socket'],
            'core_count'   => $validated['core_count'],
            'thread_count' => $validated['thread_count'],
            'base_clock'   => $validated['base_clock'],
            'boost_clock'  => $validated['boost_clock'] ?? null,
            'tdp'          => $validated['tdp'],
        ]);

        $cpu->load('component.brand');

        return response()->json([
            'id' => $cpu->id,
            'component_id' => $cpu->component_id,
            'name' => $cpu->component->name ?? '',
            'brand' => $cpu->component->brand->name ?? '',
            'price' => $cpu->component->price ?? '',
            'img_url' => $cpu->component->img_url ?? '',
            'socket' => $cpu->socket,
            'core_count' => $cpu->core_count,
            'thread_count' => $cpu->thread_count,
            'base_clock' => $cpu->base_clock,
            'boost_clock' => $cpu->boost_clock,
            'tdp' => $cpu->tdp,
        ], Response::HTTP_CREATED);
    }

    // GET /api/cpus/{cpu}
    public function show(Cpu $cpu)
    {
        $cpu->load('component.brand');

        return response()->json([
            'id' => $cpu->id,
            'component_id' => $cpu->component_id,
            'name' => $cpu->component->name ?? '',
            'brand' => $cpu->component->brand->name ?? '',
            'price' => $cpu->component->price ?? '',
            'img_url' => $cpu->component->img_url ?? '',
            'socket' => $cpu->socket,
            'core_count' => $cpu->core_count,
            'thread_count' => $cpu->thread_count,
            'base_clock' => $cpu->base_clock,
            'boost_clock' => $cpu->boost_clock,
            'tdp' => $cpu->tdp,
        ], Response::HTTP_OK);
    }

    // PUT/PATCH /api/cpus/{cpu}
    public function update(Request $request, Cpu $cpu)
    {
        $validated = $request->validate([
            // Champs du composant principal
            'name'              => 'required|string|max:255',
            'brand_id'          => 'required|exists:brands,id',
            'component_type_id' => 'required|exists:component_types,id',
            'price'             => 'nullable|numeric|min:0',
            'img_url'           => 'nullable|string',
            // Champs spécifiques CPU
            'socket'      => 'required|string|max:50',
            'core_count'  => 'required|integer|min:1',
            'thread_count'=> 'required|integer|min:1',
            'base_clock'  => 'required|numeric|min:0',
            'boost_clock' => 'nullable|numeric|min:0',
            'tdp'         => 'required|integer|min:0',
        ]);

        // Update du composant principal
        $cpu->component->update([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $validated['component_type_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

        // Update du CPU
        $cpu->update([
            'socket'       => $validated['socket'],
            'core_count'   => $validated['core_count'],
            'thread_count' => $validated['thread_count'],
            'base_clock'   => $validated['base_clock'],
            'boost_clock'  => $validated['boost_clock'] ?? null,
            'tdp'          => $validated['tdp'],
        ]);

        $cpu->load('component.brand');

        return response()->json([
            'id' => $cpu->id,
            'component_id' => $cpu->component_id,
            'name' => $cpu->component->name ?? '',
            'brand' => $cpu->component->brand->name ?? '',
            'price' => $cpu->component->price ?? '',
            'img_url' => $cpu->component->img_url ?? '',
            'socket' => $cpu->socket,
            'core_count' => $cpu->core_count,
            'thread_count' => $cpu->thread_count,
            'base_clock' => $cpu->base_clock,
            'boost_clock' => $cpu->boost_clock,
            'tdp' => $cpu->tdp,
        ], Response::HTTP_OK);
    }

    // DELETE /api/cpus/{cpu}
    public function destroy(Cpu $cpu)
    {
        $cpu->component->delete();
        $cpu->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
