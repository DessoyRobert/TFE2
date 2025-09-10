<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id' => ['required','integer','exists:users,id'],

            'customer_first_name' => ['nullable','string','max:120'],
            'customer_last_name'  => ['nullable','string','max:120'],
            'customer_email'      => ['nullable','email','max:180'],
            'customer_phone'      => ['nullable','string','max:60'],

            'shipping_address_line1' => ['nullable','string','max:255'],
            'shipping_address_line2' => ['nullable','string','max:255'],
            'shipping_city'          => ['nullable','string','max:120'],
            'shipping_postal_code'   => ['nullable','string','max:20'],
            'shipping_country'       => ['nullable','string','max:2'],

            'shipping_cost'  => ['nullable','numeric','min:0'],
            'discount_total' => ['nullable','numeric','min:0'],
            'tax_total'      => ['nullable','numeric','min:0'],
            'currency'       => ['nullable','string','size:3'],
            'status'         => ['nullable','string','max:32'],
            'payment_method' => ['nullable','string','max:64'],
            'payment_status' => ['nullable','string','max:32'],
            'meta'           => ['nullable','array'],

            'items' => ['nullable','array'],
            'items.*.purchasable_type' => ['required_with:items','string'], // ex: App\\Models\\Build
            'items.*.purchasable_id'   => ['required_with:items','integer'],
            'items.*.quantity'   => ['nullable','integer','min:1'],
            'items.*.unit_price' => ['nullable','numeric','min:0'],
            'items.*.snapshot'   => ['nullable','array'],
        ];
    }
}
