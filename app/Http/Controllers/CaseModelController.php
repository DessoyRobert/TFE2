<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;

class CaseModelController extends Controller
{
    // GET /case-models
    public function index()
    {
        return CaseModel::with('component.brand')->get()->map(function ($case) {
            return [
                'id'                => $case->id,
                'component_id'      => $case->component_id,
                'name'              => $case->component->name ?? '',
                'brand'             => $case->component->brand->name ?? '',
                'brand_id'          => $case->component->brand_id ?? '',
                'price'             => $case->component->price ?? '',
                'img_url'           => $case->component->img_url ?? '',
                'form_factor'       => $case->form_factor,
                'max_gpu_length'    => $case->max_gpu_length,
                'max_cooler_height' => $case->max_cooler_height,
            ];
        })->values();
    }

    // GET /case-models/{case}
    public function show(CaseModel $case)
    {
        $case->load('component.brand');
        return response()->json([
            'id'                => $case->id,
            'component_id'      => $case->component_id,
            'name'              => $case->component->name ?? '',
            'brand'             => $case->component->brand->name ?? '',
            'brand_id'          => $case->component->brand_id ?? '',
            'price'             => $case->component->price ?? '',
            'img_url'           => $case->component->img_url ?? '',
            'form_factor'       => $case->form_factor,
            'max_gpu_length'    => $case->max_gpu_length,
            'max_cooler_height' => $case->max_cooler_height,
        ]);
    }
}
