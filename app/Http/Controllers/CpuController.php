<?php

namespace App\Http\Controllers;

use App\Models\Cpu;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CpuController extends Controller
{
    public function index()
    {
        return response()->json(
            Cpu::with('brand')->get(),
            Response::HTTP_OK
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'brand_id'    => 'required|exists:brands,id',
            'socket'      => 'required|string|max:50',
            'cores'       => 'required|integer|min:1',
            'threads'     => 'required|integer|min:1',
            'base_clock'  => 'required|numeric|min:0',
            'boost_clock' => 'nullable|numeric|min:0',
            'tdp'         => 'required|integer|min:0',
            'price'       => 'required|numeric|min:0',
        ]);

        $cpu = Cpu::create($validated);

        return response()->json($cpu, Response::HTTP_CREATED);
    }

    public function show(Cpu $cpu)
    {
        $cpu->load('brand');

        return response()->json($cpu, Response::HTTP_OK);
    }

    public function update(Request $request, Cpu $cpu)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'brand_id'    => 'required|exists:brands,id',
            'socket'      => 'required|string|max:50',
            'cores'       => 'required|integer|min:1',
            'threads'     => 'required|integer|min:1',
            'base_clock'  => 'required|numeric|min:0',
            'boost_clock' => 'nullable|numeric|min:0',
            'tdp'         => 'required|integer|min:0',
            'price'       => 'required|numeric|min:0',
        ]);

        $cpu->update($validated);

        return response()->json($cpu, Response::HTTP_OK);
    }

    public function destroy(Cpu $cpu)
    {
        $cpu->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
