<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CaseModelController extends Controller
{
    // GET /api/casemodels
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

    // POST /api/casemodels
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'brand_id'           => 'required|exists:brands,id',
            'form_factor'        => 'required|string|max:20',
            'max_gpu_length'     => 'nullable|integer|min:0',
            'max_cooler_height'  => 'nullable|integer|min:0',
            'price'              => 'required|numeric|min:0',
            'img_url'            => 'nullable|string',
        ]);

        // Création du Component générique (type = case)
        $component = Component::create([
            'name'               => $validated['name'],
            'brand_id'           => $validated['brand_id'],
            'component_type_id'  => config('constants.component_types.case'), // adapte selon ton enum ou table
            'price'              => $validated['price'],
            'img_url'            => $validated['img_url'] ?? null,
            'description'        => null,
        ]);

        // Création du CaseModel spécifique
        $case = CaseModel::create([
            'component_id'       => $component->id,
            'form_factor'        => $validated['form_factor'],
            'max_gpu_length'     => $validated['max_gpu_length'] ?? null,
            'max_cooler_height'  => $validated['max_cooler_height'] ?? null,
        ]);

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
        ], Response::HTTP_CREATED);
    }

    // GET /api/casemodels/{case}
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
        ], Response::HTTP_OK);
    }

    // PUT/PATCH /api/casemodels/{case}
    public function update(Request $request, CaseModel $case)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'brand_id'           => 'required|exists:brands,id',
            'form_factor'        => 'required|string|max:20',
            'max_gpu_length'     => 'nullable|integer|min:0',
            'max_cooler_height'  => 'nullable|integer|min:0',
            'price'              => 'required|numeric|min:0',
            'img_url'            => 'nullable|string',
        ]);

        // Update du Component générique lié
        $case->component->update([
            'name'     => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'price'    => $validated['price'],
            'img_url'  => $validated['img_url'] ?? null,
        ]);

        // Update du CaseModel spécifique
        $case->update([
            'form_factor'        => $validated['form_factor'],
            'max_gpu_length'     => $validated['max_gpu_length'] ?? null,
            'max_cooler_height'  => $validated['max_cooler_height'] ?? null,
        ]);

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
        ], Response::HTTP_OK);
    }

    // DELETE /api/casemodels/{case}
    public function destroy(CaseModel $case)
    {
        // On supprime d'abord le component lié
        $case->component()->delete();
        // Puis le case model lui-même
        $case->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
