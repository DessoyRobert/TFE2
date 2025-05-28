<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CaseModelController extends Controller
{
    public function index()
    {
        return CaseModel::with('component')->get()->map(function ($case) {
            return [
                'id' => $case->id,
                'component_id' => $case->component_id,
                'name' => $case->component->name,
                'price' => $case->component->price,
                'img_url' => $case->component->img_url,
                'form_factor' => $case->form_factor,
            ];
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'brand_id'           => 'required|exists:brands,id',
            'form_factor'        => 'required|string|max:20',
            'max_gpu_length'     => 'nullable|integer|min:0',
            'max_cooler_height'  => 'nullable|integer|min:0',
            'price'              => 'required|numeric|min:0',
        ]);

        $case = CaseModel::create($validated);

        return response()->json($case, Response::HTTP_CREATED);
    }

    public function show(CaseModel $case)
    {
        $case->load('brand');

        return response()->json($case, Response::HTTP_OK);
    }

    public function update(Request $request, CaseModel $case)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'brand_id'           => 'required|exists:brands,id',
            'form_factor'        => 'required|string|max:20',
            'max_gpu_length'     => 'nullable|integer|min:0',
            'max_cooler_height'  => 'nullable|integer|min:0',
            'price'              => 'required|numeric|min:0',
        ]);

        $case->update($validated);

        return response()->json($case, Response::HTTP_OK);
    }

    public function destroy(CaseModel $case)
    {
        $case->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
