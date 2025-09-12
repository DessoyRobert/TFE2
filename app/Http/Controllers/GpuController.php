<?php

namespace App\Http\Controllers;

use App\Models\Gpu;

class GpuController extends Controller
{ protected string $fallbackImage =
        'https://res.cloudinary.com/djllwl8c0/image/upload/v1753292540/Logo-JarvisTech-PNG-normalsansfond_pgxlrj.png';

    // GET /gpus
    public function index()
    {
        $collection = Gpu::with(['component.brand', 'component.images'])->paginate(15);

        $collection->getCollection()->transform(function ($gpu) {
            return [
                'id'            => $gpu->component_id,
                'component_id'  => $gpu->component_id,
                'name'          => $gpu->component->name ?? '',
                'brand'         => $gpu->component->brand->name ?? '',
                'price'         => $gpu->component->price ?? '',
                'img_url'       => optional($gpu->component->images->first())->url
                                    ?? $gpu->component->img_url
                                    ?? $this->fallbackImage,
                'chipset'       => $gpu->chipset,
                'memory'        => $gpu->memory,
                'base_clock'    => $gpu->base_clock ?? null,
                'boost_clock'   => $gpu->boost_clock ?? null,
                'tdp'           => $gpu->tdp ?? null,
            ];
        })->values();

        return $collection;
    }

    // GET /gpus/{gpu}
    public function show(Gpu $gpu)
    {
        $gpu->load(['component.brand', 'component.images']);

        return response()->json([
            'id'            => $gpu->component_id,
            'component_id'  => $gpu->component_id,
            'name'          => $gpu->component->name ?? '',
            'brand'         => $gpu->component->brand->name ?? '',
            'price'         => $gpu->component->price ?? '',
            'img_url'       => optional($gpu->component->images->first())->url
                                ?? $gpu->component->img_url
                                ?? $this->fallbackImage,
            'chipset'       => $gpu->chipset,
            'memory'        => $gpu->memory,
            'base_clock'    => $gpu->base_clock ?? null,
            'boost_clock'   => $gpu->boost_clock ?? null,
            'tdp'           => $gpu->tdp ?? null,
        ]);
    }
}
