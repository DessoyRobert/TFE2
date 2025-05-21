<?php

namespace App\Http\Controllers;

use App\Models\Cpu;
use App\Models\Component;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CpuController extends Controller
{
    public function index()
    {
        $cpus = Cpu::with('component')->get();
        return Inertia::render('Cpus/Index', [
            'cpus' => $cpus,
        ]);
    }

    public function show(Cpu $cpu)
    {
        $cpu->load('component');
        return Inertia::render('Cpus/Show', [
            'cpu' => $cpu,
        ]);
    }

    public function create()
    {
        // On peut proposer la création d’un composant ici aussi, à adapter selon ton flow
        return Inertia::render('Cpus/Create');
    }

    public function store(Request $request)
    {
        $validatedComponent = $request->validate([
            'component.name' => 'required',
            'component.brand' => 'required',
            'component.type' => 'required|in:cpu',
            'component.price' => 'nullable|numeric',
            'component.img_url' => 'nullable|string',
            'component.description' => 'nullable|string',
            'component.release_year' => 'nullable|integer',
            'component.ean' => 'nullable|string'
        ]);
        $validatedCpu = $request->validate([
            'socket' => 'required',
            'core_count' => 'required|integer',
            'thread_count' => 'required|integer',
            'base_clock' => 'nullable|numeric',
            'boost_clock' => 'nullable|numeric',
            'tdp' => 'nullable|integer',
            'integrated_graphics' => 'nullable|string'
        ]);
        // Créer d'abord le component parent
        $component = Component::create($validatedComponent['component']);
        // Puis le CPU lié
        $validatedCpu['component_id'] = $component->id;
        Cpu::create($validatedCpu);

        return redirect()->route('cpus.index');
    }

    public function edit(Cpu $cpu)
    {
        $cpu->load('component');
        return Inertia::render('Cpus/Edit', [
            'cpu' => $cpu
        ]);
    }

    public function update(Request $request, Cpu $cpu)
    {
        $validatedComponent = $request->validate([
            'component.name' => 'required',
            'component.brand' => 'required',
            'component.type' => 'required|in:cpu',
            'component.price' => 'nullable|numeric',
            'component.img_url' => 'nullable|string',
            'component.description' => 'nullable|string',
            'component.release_year' => 'nullable|integer',
            'component.ean' => 'nullable|string'
        ]);
        $validatedCpu = $request->validate([
            'socket' => 'required',
            'core_count' => 'required|integer',
            'thread_count' => 'required|integer',
            'base_clock' => 'nullable|numeric',
            'boost_clock' => 'nullable|numeric',
            'tdp' => 'nullable|integer',
            'integrated_graphics' => 'nullable|string'
        ]);
        // Update component
        $cpu->component->update($validatedComponent['component']);
        // Update cpu
        $cpu->update($validatedCpu);

        return redirect()->route('cpus.index');
    }

    public function destroy(Cpu $cpu)
    {
        // Supprime aussi le component parent
        $cpu->component->delete();
        $cpu->delete();
        return redirect()->route('cpus.index');
    }
}
