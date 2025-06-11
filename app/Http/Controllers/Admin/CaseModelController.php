<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CaseModelController extends Controller
{
    // POST /admin/case-models
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

        $component = Component::create([
            'name'              => $validated['name'],
            'brand_id'          => $validated['brand_id'],
            'component_type_id' => config('constants.component_types.case'),
            'price'             => $validated['price'],
            'img_url'           => $validated['img_url'] ?? null,
            'description'       => null,
        ]);

        $case = CaseModel::create([
            'component_id'      => $component->id,
            'form_factor'       => $validated['form_factor'],
            'max_gpu_length'    => $validated['max_gpu_length'] ?? null,
            'max_cooler_height' => $validated['max_cooler_height'] ?? null,
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

    // PUT/PATCH /admin/case-models/{case}
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

        $case->component->update([
            'name'     => $validated['name'],
            'brand_id' => $validated['brand_id'],
            'price'    => $validated['price'],
            'img_url'  => $validated['img_url'] ?? null,
        ]);

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

    // DELETE /admin/case-models/{case}
    public function destroy(CaseModel $case)
    {
        $case->component()->delete();
        $case->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
