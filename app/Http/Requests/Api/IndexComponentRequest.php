<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class IndexComponentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'search'    => ['nullable','string','max:200'],
            'per_page'  => ['nullable','integer','min:1','max:100'],
            'sortBy'    => ['nullable','string','in:id,name,price'],
            'sortDesc'  => ['nullable'], // bool lu via $request->boolean()
        ];
    }
}
