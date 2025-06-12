<?php

namespace App\Http\Controllers;

use App\Models\Cooler;

class CoolerController extends Controller
{
    public function index()
    {
        return Cooler::with('component.brand')->get()->map(function ($cooler) {
            return [
                'id' => $cooler->component_id,
                'component_id' => $cooler->component_id,
                'name' => $cooler->component->name ?? '',
                'brand' => $cooler->component->brand->name ?? '',
                'price' => $cooler->component->price ?? '',
                'img_url' => $cooler->component->img_url ?? '',
                'type' => $cooler->type,
                'fan_count' => $cooler->fan_count,
            ];
        })->values();
    }

    public function show(Cooler $cooler)
    {
        $cooler->load('component.brand');
        return response()->json([
            'id' => $cooler->component_id,
            'component_id' => $cooler->component_id,
            'name' => $cooler->component->name ?? '',
            'brand' => $cooler->component->brand->name ?? '',
            'price' => $cooler->component->price ?? '',
            'img_url' => $cooler->component->img_url ?? '',
            'type' => $cooler->type,
            'fan_count' => $cooler->fan_count,
        ]);
    }
}
