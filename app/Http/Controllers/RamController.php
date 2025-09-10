<?php

namespace App\Http\Controllers;

use App\Models\Ram;

class RamController extends Controller
{
    public function index()
    {
        $collection = Ram::with(['component.brand', 'component.images'])->paginate(15);

        $collection->getCollection()->transform(function ($ram) {
            return [
                'id' => $ram->component_id,
                'component_id' => $ram->component_id,
                'name' => $ram->component->name ?? '',
                'brand' => $ram->component->brand->name ?? '',
                'price' => $ram->component->price ?? '',
                'img_url' => optional($ram->component->images->first())->url
                    ?? $ram->component->img_url
                    ?? '/images/default.png',
                'type' => $ram->type,
                'capacity_gb' => $ram->capacity_gb,
                'modules' => $ram->modules,
                'speed_mhz' => $ram->speed_mhz,
                'cas_latency' => $ram->cas_latency,
            ];
        })->values();

        return $collection;
    }

    public function show(Ram $ram)
    {
        $ram->load(['component.brand', 'component.images']);

        return response()->json([
            'id' => $ram->component_id,
            'component_id' => $ram->component_id,
            'name' => $ram->component->name ?? '',
            'brand' => $ram->component->brand->name ?? '',
            'price' => $ram->component->price ?? '',
            'img_url' => optional($ram->component->images->first())->url
                ?? $ram->component->img_url
                ?? '/images/default.png',
            'type' => $ram->type,
            'capacity_gb' => $ram->capacity_gb,
            'modules' => $ram->modules,
            'speed_mhz' => $ram->speed_mhz,
            'cas_latency' => $ram->cas_latency,
        ]);
    }
}
