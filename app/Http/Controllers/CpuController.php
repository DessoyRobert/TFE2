<?php

namespace App\Http\Controllers;

use App\Models\Cpu;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CpuController extends Controller
{
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'brand_id'     => 'required|exists:brands,id',
            'price'        => 'nullable|numeric|min:0',
            'img_url'      => 'nullable|string',
            'socket'       => 'required|string|max:50',
            'core_count'   => 'required|integer|min:1',
            'thread_count' => 'required|integer|min:1',
            'base_clock'   => 'required|numeric|min:0',
            'boost_clock'  => 'nullable|numeric|min:0',
            'tdp'          => 'required|integer|min:0',
        ]);

        $componentTypeId = \App\Models\ComponentType::where('name', 'cpu')->first()->id;

        $component = \App\Models\Component::create([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'component_type_id' => $componentTypeId,
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

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

    public function update(Request $request, Cpu $cpu)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'brand_id'     => 'required|exists:brands,id',
            'price'        => 'nullable|numeric|min:0',
            'img_url'      => 'nullable|string',
            'socket'       => 'required|string|max:50',
            'core_count'   => 'required|integer|min:1',
            'thread_count' => 'required|integer|min:1',
            'base_clock'   => 'required|numeric|min:0',
            'boost_clock'  => 'nullable|numeric|min:0',
            'tdp'          => 'required|integer|min:0',
        ]);

        $cpu->component->update([
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'price' => $validated['price'] ?? null,
            'img_url' => $validated['img_url'] ?? null,
        ]);

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

    public function destroy(Cpu $cpu)
    {
        $cpu->component->delete();
        $cpu->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
