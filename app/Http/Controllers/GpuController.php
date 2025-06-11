<?php

namespace App\Http\Controllers;

use App\Models\Gpu;

class GpuController extends Controller
{
    // GET /gpus
    public function index()
    {
        return Gpu::with('component.brand')->get()->map(function ($gpu) {
            return [
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
            ];
        })->values();
    }

    // GET /gpus/{gpu}
    public function show(Gpu $gpu)
    {
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
        ]);
    }
}
