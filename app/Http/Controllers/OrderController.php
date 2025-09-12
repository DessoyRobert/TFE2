<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OrderController extends Controller
{
    /**
     * GET /checkout/{order}
     * Hydrate Checkout/Index.vue via la prop serverResult (même shape que l'API /api/checkout)
     */
    public function show(Request $request, Order $order): InertiaResponse
    {
        $user = Auth::user();

        // Autoriser propriétaire OU admin (404 pour ne pas révéler l'existence)
        if (!$user || ($user->id !== (int) $order->user_id && !($user->is_admin ?? false))) {
            abort(404);
        }

        // Charger items + dernier paiement (offline virement)
        $order->load([
            'items',
            'payments' => fn($q) => $q->latest(),
        ]);

        $p = $order->payments->first();

        // Bloc virement (priorité aux données du Payment si présent)
        $bank = null;
        if ($order->payment_method === 'bank_transfer') {
            if ($p && $p->method === 'bank_transfer') {
                $bank = [
                    'holder'           => $p->meta['beneficiary'] ?? config('services.bank.holder', 'PCBuilder SRL'),
                    'iban'             => $p->meta['iban'] ?? config('services.bank.iban', 'BE00 0000 0000 0000'),
                    'bic'              => $p->meta['bic'] ?? config('services.bank.bic', 'XXXXXX'),
                    'reference'        => $p->meta['reference'] ?? ('ORDER-' . $order->id),
                    'payment_deadline' => $p->meta['due_date'] ?? null,
                ];
            } else {
                // Fallback si pas de payment en base
                $bank = [
                    'holder'           => config('services.bank.holder', 'PCBuilder SRL'),
                    'iban'             => config('services.bank.iban', 'BE00 0000 0000 0000'),
                    'bic'              => config('services.bank.bic', 'XXXXXX'),
                    'reference'        => $order->bank_reference ?? ('ORDER-' . $order->id),
                    'payment_deadline' => optional($order->payment_deadline)->format('Y-m-d'),
                ];
            }
        }

        // Items au format attendu par le front
        $itemsPayload = $order->items->map(function ($it) {
            $name = $it->snapshot['name'] ?? null;
            return [
                'type'       => class_basename($it->purchasable_type), // "Build" ou "Component"
                'id'         => (int) $it->purchasable_id,
                'name'       => $name,
                'quantity'   => (int) $it->quantity,
                'unit_price' => (string) $it->unit_price,
                'line_total' => (string) $it->line_total,
            ];
        })->values();

        // Même shape que la réponse JSON de /api/checkout
        $serverResult = [
            'order_id'       => $order->id,
            'status'         => $order->status,
            'payment_status' => $order->payment_status,
            'currency'       => $order->currency,
            'amounts'        => [
                'subtotal' => (string) $order->subtotal,
                'shipping' => (string) $order->shipping_cost,
                'tax'      => (string) $order->tax_total,
                'discount' => (string) $order->discount_total,
                'grand'    => (string) $order->grand_total,
            ],
            'items'        => $itemsPayload,
            'bank'         => $bank,
            'redirect_url' => null,
        ];

        return Inertia::render('Checkout/Index', [
            'serverResult' => $serverResult,
        ]);
    }
}
