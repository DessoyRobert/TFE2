<?php

namespace App\Http\Controllers;

use App\Models\Cooler;

class CoolerController extends Controller
{
    // GET /coolers
    public function index()
    {
        return Cooler::with('component.brand')->get()->map(function ($cooler) {
            return [
                'id' => $cooler->id,
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

    // GET /coolers/{cooler}
    public function show(Cooler $cooler)
    {
        $cooler->load('component.brand');
        return response()->json([
            'id' => $cooler->id,
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
