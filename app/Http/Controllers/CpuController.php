<?php

namespace App\Http\Controllers;

use App\Models\Cpu;

class CpuController extends Controller
{
     protected string $fallbackImage =
        'https://res.cloudinary.com/djllwl8c0/image/upload/v1753292540/Logo-JarvisTech-PNG-normalsansfond_pgxlrj.png';

    // GET /cpus
    public function index()
    {
        $collection = Cpu::with(['component.brand', 'component.images'])->paginate(15);

        $collection->getCollection()->transform(function ($cpu) {
            return [
                'id'            => $cpu->component_id,
                'component_id'  => $cpu->component_id,
                'name'          => $cpu->component->name ?? '',
                'brand'         => $cpu->component->brand->name ?? '',
                'price'         => $cpu->component->price ?? '',
                'img_url'       => optional($cpu->component->images->first())->url
                                    ?? $cpu->component->img_url
                                    ?? $this->fallbackImage,
                'socket'        => $cpu->socket,
                'core_count'    => $cpu->core_count,
                'thread_count'  => $cpu->thread_count,
                'base_clock'    => $cpu->base_clock,
                'boost_clock'   => $cpu->boost_clock,
                'tdp'           => $cpu->tdp,
            ];
        })->values();

        return $collection;
    }

    // GET /cpus/{cpu}
    public function show(Cpu $cpu)
    {
        $cpu->load(['component.brand', 'component.images']);

        return response()->json([
            'id'            => $cpu->component_id,
            'component_id'  => $cpu->component_id,
            'name'          => $cpu->component->name ?? '',
            'brand'         => $cpu->component->brand->name ?? '',
            'price'         => $cpu->component->price ?? '',
            'img_url'       => optional($cpu->component->images->first())->url
                                ?? $cpu->component->img_url
                                ?? $this->fallbackImage,
            'socket'        => $cpu->socket,
            'core_count'    => $cpu->core_count,
            'thread_count'  => $cpu->thread_count,
            'base_clock'    => $cpu->base_clock,
            'boost_clock'   => $cpu->boost_clock,
            'tdp'           => $cpu->tdp,
        ]);
    }
}
