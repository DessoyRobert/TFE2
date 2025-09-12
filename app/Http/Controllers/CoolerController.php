<?php

namespace App\Http\Controllers;

use App\Models\Cooler;

class CoolerController extends Controller
{protected string $fallbackImage =
        'https://res.cloudinary.com/djllwl8c0/image/upload/v1753292540/Logo-JarvisTech-PNG-normalsansfond_pgxlrj.png';
    public function index()
    {
         

        $collection = Cooler::with(['component.brand', 'component.images'])->paginate(15);

        $collection->getCollection()->transform(function ($cooler) {
            return [
                'id'            => $cooler->component_id,
                'component_id'  => $cooler->component_id,
                'name'          => $cooler->component->name ?? '',
                'brand'         => $cooler->component->brand->name ?? '',
                'price'         => $cooler->component->price ?? '',
                'img_url'       => optional($cooler->component->images->first())->url
                                    ?? $cooler->component->img_url
                                    ?? $this->fallbackImage,
                'type'          => $cooler->type,
                'fan_count'     => $cooler->fan_count,
            ];
        })->values();

        return $collection;
    }

    public function show(Cooler $cooler)
    {
        $cooler->load(['component.brand', 'component.images']);

        return response()->json([
            'id'            => $cooler->component_id,
            'component_id'  => $cooler->component_id,
            'name'          => $cooler->component->name ?? '',
            'brand'         => $cooler->component->brand->name ?? '',
            'price'         => $cooler->component->price ?? '',
            'img_url'       => optional($cooler->component->images->first())->url
                                ?? $cooler->component->img_url
                                ?? $this->fallbackImage,
            'type'          => $cooler->type,
            'fan_count'     => $cooler->fan_count,
        ]);
    }
}
