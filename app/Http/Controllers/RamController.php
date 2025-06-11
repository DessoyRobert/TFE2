<?php

namespace App\Http\Controllers;

use App\Models\Ram;

class RamController extends Controller
{
    // GET /rams
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

    // GET /rams/{ram}
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
        ]);
    }
}
