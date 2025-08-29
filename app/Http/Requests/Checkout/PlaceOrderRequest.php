<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $buildId = (int) $this->input('build_id');

        return \App\Models\Build::where('id', $buildId)
            ->where('user_id', $this->user()?->id)
            ->exists();
    }

    public function rules(): array
    {
        return [
            'build_id'        => ['required','integer','exists:builds,id'],
            'component_ids'   => ['nullable','array'],
            'component_ids.*' => ['integer','exists:components,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'build_id.required' => 'Le build est requis.',
            'build_id.exists'   => 'Ce build est introuvable.',
        ];
    }
}
