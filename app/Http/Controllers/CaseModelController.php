<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;

class CaseModelController extends Controller
{
      protected string $fallbackImage =
        'https://res.cloudinary.com/djllwl8c0/image/upload/v1753292540/Logo-JarvisTech-PNG-normalsansfond_pgxlrj.png';

    /**
     * Retourne une liste paginée de boîtiers (CaseModels) avec leurs composants,
     * marques et images Cloudinary.
     */
    public function index()
    {
        $collection = CaseModel::with(['component.brand', 'component.images'])
            ->paginate(15);

        $collection->getCollection()->transform(function ($case) {
            return [
                'id'                => $case->component_id,
                'component_id'      => $case->component_id,
                'name'              => $case->component->name ?? '',
                'brand'             => $case->component->brand->name ?? '',
                'brand_id'          => $case->component->brand_id ?? '',
                'price'             => $case->component->price ?? '',
                // On prend la 1ère image Cloudinary si dispo, sinon fallback
                'img_url'           => optional($case->component->images->first())->url 
                                        ?? $case->component->img_url 
                                        ?? $this->fallbackImage,
                'form_factor'       => $case->form_factor,
                'max_gpu_length'    => $case->max_gpu_length,
                'max_cooler_height' => $case->max_cooler_height,
            ];
        })->values();

        return $collection;
    }

    /**
     * Retourne un boîtier précis avec toutes ses infos et images.
     */
    public function show(CaseModel $case)
    {
        $case->load(['component.brand', 'component.images']);

        return response()->json([
            'id'                => $case->component_id,
            'component_id'      => $case->component_id,
            'name'              => $case->component->name ?? '',
            'brand'             => $case->component->brand->name ?? '',
            'brand_id'          => $case->component->brand_id ?? '',
            'price'             => $case->component->price ?? '',
            'img_url'           => optional($case->component->images->first())->url 
                                    ?? $case->component->img_url 
                                    ?? $this->fallbackImage,
            'form_factor'       => $case->form_factor,
            'max_gpu_length'    => $case->max_gpu_length,
            'max_cooler_height' => $case->max_cooler_height,
        ]);
    }
}
