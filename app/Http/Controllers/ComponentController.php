<?php

namespace App\Http\Controllers;

use App\Models\Component;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ComponentController extends Controller
{
    public function index()
    {
        // Liste tous les composants
        $components = Component::with(['cpu', 'gpu', 'ram', 'motherboard', 'storage', 'psu', 'cooler', 'caseModel'])->get();
        return Inertia::render('Components/Index', [
            'components' => $components,
        ]);
    }

    public function show(Component $component)
    {
        // Affiche un composant (détail)
        $component->load(['cpu', 'gpu', 'ram', 'motherboard', 'storage', 'psu', 'cooler', 'caseModel']);
        return Inertia::render('Components/Show', [
            'component' => $component,
        ]);
    }

    public function create()
    {
        return Inertia::render('Components/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'brand' => 'required',
            'type' => 'required|in:cpu,gpu,ram,motherboard,storage,psu,cooler,case',
            'price' => 'nullable|numeric',
            'img_url' => 'nullable|string',
            'description' => 'nullable|string',
            'release_year' => 'nullable|integer',
            'ean' => 'nullable|string'
        ]);
        $component = Component::create($validated);
        return redirect()->route('components.index');
    }

    public function edit(Component $component)
    {
        return Inertia::render('Components/Edit', [
            'component' => $component
        ]);
    }

    public function update(Request $request, Component $component)
    {
        $validated = $request->validate([
            'name' => 'required',
            'brand' => 'required',
            'type' => 'required|in:cpu,gpu,ram,motherboard,storage,psu,cooler,case',
            'price' => 'nullable|numeric',
            'img_url' => 'nullable|string',
            'description' => 'nullable|string',
            'release_year' => 'nullable|integer',
            'ean' => 'nullable|string'
        ]);
        $component->update($validated);
        return redirect()->route('components.index');
    }

    public function destroy(Component $component)
    {
        $component->delete();
        return redirect()->route('components.index');
    }
}
