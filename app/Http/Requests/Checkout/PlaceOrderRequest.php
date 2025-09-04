<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        // Cast/clean des IDs
        if (is_array($this->input('component_ids'))) {
            $this->merge([
                'component_ids' => collect($this->input('component_ids'))
                    ->map(fn ($v) => is_numeric($v) ? (int) $v : null)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all(),
            ]);
        }

        if ($this->has('build_id') && is_numeric($this->build_id)) {
            $this->merge(['build_id' => (int) $this->build_id]);
        }

        // Normalisation simple
        if ($this->has('shipping_country')) {
            $this->merge(['shipping_country' => strtoupper((string) $this->shipping_country)]);
        }
        if ($this->has('currency')) {
            $this->merge(['currency' => strtoupper((string) $this->currency)]);
        }
    }

    public function rules(): array
    {
        return [
            'build_id'        => ['required', 'integer', 'exists:builds,id'],
            'component_ids'   => ['sometimes', 'array'],
            'component_ids.*' => ['integer', 'distinct', 'exists:components,id'],

            // Faculatif: infos client / adresse (le contrôleur gère les fallback)
            'customer_first_name'    => ['sometimes','string','max:255'],
            'customer_last_name'     => ['sometimes','string','max:255'],
            'customer_email'         => ['sometimes','email','max:255'],
            'customer_phone'         => ['sometimes','string','max:50'],
            'shipping_address_line1' => ['sometimes','string','max:255'],
            'shipping_address_line2' => ['sometimes','nullable','string','max:255'],
            'shipping_city'          => ['sometimes','string','max:120'],
            'shipping_postal_code'   => ['sometimes','string','max:20'],
            'shipping_country'       => ['sometimes','string','size:2'],

            'payment_method'         => ['sometimes','in:bank_transfer'], // à élargir si besoin
            'currency'               => ['sometimes','in:EUR'],           // à élargir si besoin
        ];
    }

    public function messages(): array
    {
        return [
            'build_id.required' => 'Le build est requis.',
            'build_id.exists'   => 'Le build sélectionné est introuvable.',
            'component_ids.*.exists' => 'Un composant sélectionné est introuvable.',
        ];
    }
}
