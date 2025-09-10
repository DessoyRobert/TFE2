<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderAddItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'purchasable_type' => ['required','string'],
            'purchasable_id'   => ['required','integer'],
            'quantity'         => ['nullable','integer','min:1'],
            'unit_price'       => ['required','numeric','min:0'],
            'snapshot'         => ['nullable','array'],
        ];
    }
}
