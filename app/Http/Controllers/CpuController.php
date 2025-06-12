<?php

namespace App\Http\Controllers;

use App\Models\Cpu;

class CpuController extends Controller
{
    // GET /cpus
    public function index()
    {
        return Cpu::with('component.brand')->get()->map(function ($cpu) {
            return [
                'id' => $cpu->component_id, 
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

    // GET /cpus/{cpu}
    public function show(Cpu $cpu)
    {
        $cpu->load('component.brand');
        return response()->json([
            'id' => $cpu->component_id, 
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
        ]);
    }
}
