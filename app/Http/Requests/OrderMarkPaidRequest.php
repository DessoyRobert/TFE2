<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderMarkPaidRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'provider'     => ['nullable','string','max:64'],
            'provider_ref' => ['nullable','string','max:191'],
            'amount'       => ['nullable','numeric','min:0'],
            'currency'     => ['nullable','string','size:3'],
            'status'       => ['nullable','string','max:32'], // succeeded, failed, refunded
            'payload'      => ['nullable','array'],
        ];
    }
}
