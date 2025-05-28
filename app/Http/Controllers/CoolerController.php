<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CoolerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
        public function index()
    {
        return Cooler::with('component')->get()->map(function ($cooler) {
            return [
                'id' => $cooler->id,
                'component_id' => $cooler->component_id,
                'name' => $cooler->component->name,
                'price' => $cooler->component->price,
                'img_url' => $cooler->component->img_url,
                'type' => $cooler->type,
                'fan_count' => $cooler->fan_count,
            ];
        });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
