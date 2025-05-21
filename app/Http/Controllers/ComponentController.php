<?php

namespace App\Http\Controllers;

use App\Models\Component;
use App\Models\Cpu;
use App\Models\Gpu;
use App\Models\Ram;
use App\Models\Motherboard;
use App\Models\Storage;
use App\Models\Psu;
use App\Models\Cooler;
use App\Models\CaseModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ComponentController extends Controller
{
    public function index()
    {
        $components = Component::with([
            'cpu', 'gpu', 'ram', 'motherboard', 'storage', 'psu', 'cooler', 'caseModel'
        ])->get();

        return Inertia::render('Components/Index', ['components' => $components]);
    }

    public function create()
    {
        return Inertia::render('Components/Create');
    }

    public function store(Request $request)
    {
        $componentData = $request->validate([
            'component.name' => 'required',
            'component.brand' => 'required',
            'component.type' => 'required|in:cpu,gpu,ram,motherboard,storage,psu,cooler,case',
            'component.price' => 'nullable|numeric',
            'component.img_url' => 'nullable|string',
            'component.description' => 'nullable|string',
            'component.release_year' => 'nullable|integer',
            'component.ean' => 'nullable|string'
        ]);
        $type = $componentData['component']['type'];
        $component = Component::create($componentData['component']);

        switch ($type) {
            case 'cpu':
                $cpuData = $request->validate([
                    'socket' => 'required',
                    'core_count' => 'required|integer',
                    'thread_count' => 'required|integer',
                    'base_clock' => 'nullable|numeric',
                    'boost_clock' => 'nullable|numeric',
                    'tdp' => 'nullable|integer',
                    'integrated_graphics' => 'nullable|string',
                ]);
                $cpuData['component_id'] = $component->id;
                Cpu::create($cpuData);
                break;
            case 'gpu':
                $gpuData = $request->validate([
                    'chipset' => 'required',
                    'vram' => 'required|integer',
                    'base_clock' => 'nullable|integer',
                    'boost_clock' => 'nullable|integer',
                    'tdp' => 'nullable|integer',
                    'length_mm' => 'nullable|integer',
                ]);
                $gpuData['component_id'] = $component->id;
                Gpu::create($gpuData);
                break;
            case 'ram':
                $ramData = $request->validate([
                    'capacity' => 'required|integer',
                    'speed' => 'nullable|integer',
                    'ram_type' => 'required|string',
                    'form_factor' => 'nullable|string',
                    'modules' => 'nullable|integer',
                ]);
                $ramData['component_id'] = $component->id;
                Ram::create($ramData);
                break;
            case 'motherboard':
                $motherboardData = $request->validate([
                    'socket' => 'required',
                    'form_factor' => 'nullable|string',
                    'ram_slots' => 'nullable|integer',
                    'max_ram' => 'nullable|integer',
                    'chipset' => 'nullable|string',
                ]);
                $motherboardData['component_id'] = $component->id;
                Motherboard::create($motherboardData);
                break;
                            case 'storage':
                $storageData = $request->validate([
                    'capacity' => 'required|integer',
                    'storage_type' => 'required|string',
                    'form_factor' => 'nullable|string',
                    'interface' => 'nullable|string',
                ]);
                $storageData['component_id'] = $component->id;
                Storage::create($storageData);
                break;

            case 'psu':
                $psuData = $request->validate([
                    'wattage' => 'required|integer',
                    'efficiency_rating' => 'nullable|string',
                    'modular' => 'nullable|boolean',
                ]);
                $psuData['component_id'] = $component->id;
                Psu::create($psuData);
                break;

            case 'cooler':
                $coolerData = $request->validate([
                    'cooler_type' => 'required|string',
                    'socket_compatibility' => 'nullable|string',
                    'height_mm' => 'nullable|integer',
                    'fan_size_mm' => 'nullable|integer',
                ]);
                $coolerData['component_id'] = $component->id;
                Cooler::create($coolerData);
                break;

            case 'case':
                $caseData = $request->validate([
                    'form_factor' => 'required|string',
                    'max_gpu_length_mm' => 'nullable|integer',
                    'max_cpu_cooler_height_mm' => 'nullable|integer',
                    'drive_bays' => 'nullable|integer',
                ]);
                $caseData['component_id'] = $component->id;
                CaseModel::create($caseData);
                break;

            default:
                // logger une erreur ou lancer une exception
                break;
        }

        // 4. Retour à la liste
        return redirect()->route('components.index');
    }

    // ========== BONUS : Méthodes à compléter pour le CRUD complet ==========

    public function edit(Component $component)
    {
        $component->load([
            'cpu', 'gpu', 'ram', 'motherboard', 'storage', 'psu', 'cooler', 'caseModel'
        ]);
        return Inertia::render('Components/Edit', ['component' => $component]);
    }

    public function update(Request $request, Component $component)
{
    $componentData = $request->validate([
        'component.name' => 'required',
        'component.brand' => 'required',
        'component.type' => 'required|in:cpu,gpu,ram,motherboard,storage,psu,cooler,case',
        'component.price' => 'nullable|numeric',
        'component.img_url' => 'nullable|string',
        'component.description' => 'nullable|string',
        'component.release_year' => 'nullable|integer',
        'component.ean' => 'nullable|string'
    ]);
    $type = $componentData['component']['type'];
    $component->update($componentData['component']);

    switch ($type) {
        case 'cpu':
            $cpuData = $request->validate([
                'socket' => 'required',
                'core_count' => 'required|integer',
                'thread_count' => 'required|integer',
                'base_clock' => 'nullable|numeric',
                'boost_clock' => 'nullable|numeric',
                'tdp' => 'nullable|integer',
                'integrated_graphics' => 'nullable|string',
            ]);
            $component->cpu()->update($cpuData);
            break;
        case 'gpu':
            $gpuData = $request->validate([
                'chipset' => 'required',
                'vram' => 'required|integer',
                'base_clock' => 'nullable|integer',
                'boost_clock' => 'nullable|integer',
                'tdp' => 'nullable|integer',
                'length_mm' => 'nullable|integer',
            ]);
            $component->gpu()->update($gpuData);
            break;
        case 'ram':
            $ramData = $request->validate([
                'capacity' => 'required|integer',
                'speed' => 'nullable|integer',
                'ram_type' => 'required|string',
                'form_factor' => 'nullable|string',
                'modules' => 'nullable|integer',
            ]);
            $component->ram()->update($ramData);
            break;
        case 'motherboard':
            $motherboardData = $request->validate([
                'socket' => 'required',
                'form_factor' => 'nullable|string',
                'ram_slots' => 'nullable|integer',
                'max_ram' => 'nullable|integer',
                'chipset' => 'nullable|string',
            ]);
            $component->motherboard()->update($motherboardData);
            break;
        case 'storage':
            $storageData = $request->validate([
                'capacity' => 'required|integer',
                'storage_type' => 'required|string',
                'form_factor' => 'nullable|string',
                'interface' => 'nullable|string',
            ]);
            $component->storage()->update($storageData);
            break;
        case 'psu':
            $psuData = $request->validate([
                'wattage' => 'required|integer',
                'efficiency_rating' => 'nullable|string',
                'modular' => 'nullable|boolean',
            ]);
            $component->psu()->update($psuData);
            break;
        case 'cooler':
            $coolerData = $request->validate([
                'cooler_type' => 'required|string',
                'socket_compatibility' => 'nullable|string',
                'height_mm' => 'nullable|integer',
                'fan_size_mm' => 'nullable|integer',
            ]);
            $component->cooler()->update($coolerData);
            break;
        case 'case':
            $caseData = $request->validate([
                'form_factor' => 'required|string',
                'max_gpu_length_mm' => 'nullable|integer',
                'max_cpu_cooler_height_mm' => 'nullable|integer',
                'drive_bays' => 'nullable|integer',
            ]);
            $component->caseModel()->update($caseData);
            break;

        default:
            // logger une erreur ou lancer une exception
            break;
    }

    return redirect()->route('components.index');
}


    public function destroy(Component $component)
    {
        // Supprime d'abord le modèle enfant associé selon le type
        switch ($component->type) {
            case 'cpu': $component->cpu()->delete(); break;
            case 'gpu': $component->gpu()->delete(); break;
            case 'ram': $component->ram()->delete(); break;
            case 'motherboard': $component->motherboard()->delete(); break;
            case 'storage': $component->storage()->delete(); break;
            case 'psu': $component->psu()->delete(); break;
            case 'cooler': $component->cooler()->delete(); break;
            case 'case': $component->caseModel()->delete(); break;
        }
        // Puis le parent
        $component->delete();

        return redirect()->route('components.index');
    }
}
