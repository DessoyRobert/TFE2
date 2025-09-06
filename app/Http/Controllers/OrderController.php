<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OrderController extends Controller
{
    public function show(Request $request, Order $order): InertiaResponse
    {
        // Sécurité: l'acheteur uniquement (ou adapte avec Policy)
        abort_unless($request->user() && $request->user()->id === (int) $order->user_id, 403);

        // Charge le minimum utile à l’écran
        $order->load(['items.purchasable']);

        // Map vers la même forme que l’API de CheckoutController@store
        $serverResult = [
            'order_id'       => $order->id,
            'status'         => $order->status,
            'payment_status' => $order->payment_status,
            'currency'       => $order->currency,
            'amounts' => [
                'subtotal' => (string) $order->subtotal,
                'shipping' => (string) $order->shipping_cost,
                'tax'      => (string) $order->tax_total,
                'discount' => (string) $order->discount_total,
                'grand'    => (string) $order->grand_total,
            ],
            'bank' => null,
            'redirect_url' => null,
        ];

        // Si méthode virement, reconstitue le bloc banque (même logique que l’API)
        if ($order->payment_method === 'bank_transfer') {
            $serverResult['bank'] = [
                'holder'           => env('BANK_HOLDER', 'PCBuilder SRL'),
                'iban'             => env('BANK_IBAN', 'BE00 0000 0000 0000'),
                'bic'              => env('BANK_BIC', 'XXXXXX'),
                'reference'        => 'ORDER-' . $order->id,
                'payment_deadline' => optional($order->payment_deadline ?? now()->addDays((int) env('BANK_PAYMENT_DAYS', 7)))->format('Y-m-d'),
            ];
        }

        return Inertia::render('Checkout/Index', [
            'serverResult' => $serverResult,
        ]);
    }
}
