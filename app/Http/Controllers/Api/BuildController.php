<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Build;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BuildController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:150'],
            'description' => ['nullable','string','max:2000'],
            'img_url'     => ['nullable','string','max:1000'],
            'price'       => ['nullable','numeric','min:0'],
            'components'  => ['required','array','min:1'],
            'components.*.component_id' => ['required','integer','min:1'],
        ]);

        $build = Build::create([
            'user_id'     => optional($request->user())->id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'img_url'     => $data['img_url'] ?? null,
            'price'       => $data['price'] ?? 0,
        ]);

        $ids = collect($data['components'])->pluck('component_id')->filter()->unique()->values();
        $build->components()->sync($ids);

        return response()->json([
            'id'   => $build->id,
            'name' => $build->name,
        ], 201);
    }
}
