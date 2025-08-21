<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Build;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    /**
     * Reçoit un panier minimal: items[] = [{ type: "build"|"component", id: number, qty: number }]
     * + infos client + adresse. Recalcule tout côté serveur.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_first_name' => ['required','string','max:100'],
            'customer_last_name'  => ['required','string','max:100'],
            'customer_email'      => ['required','email','max:150'],
            'customer_phone'      => ['nullable','string','max:50'],

            'shipping_address_line1' => ['required','string','max:200'],
            'shipping_address_line2' => ['nullable','string','max:200'],
            'shipping_city'          => ['required','string','max:120'],
            'shipping_postal_code'   => ['required','string','max:20'],
            'shipping_country'       => ['required','string','size:2'],

            'currency'        => ['nullable','string','size:3'],
            'payment_method'  => ['nullable','string','max:50'],

            'items'           => ['required','array','min:1'],
            'items.*.type'    => ['required', Rule::in(['build','component'])],
            'items.*.id'      => ['required','integer','min:1'],
            'items.*.qty'     => ['required','integer','min:1','max:99'],
        ]);

        $userId   = optional($request->user())->id;
        $currency = $validated['currency'] ?? 'EUR';

        return DB::transaction(function () use ($validated, $userId, $currency) {
            $subtotal  = 0.0;
            $lineItems = [];

            foreach ($validated['items'] as $idx => $it) {
                try {
                    if ($it['type'] === 'build') {
                        $model = Build::query()->findOrFail($it['id']);
                        $unit  = (float) ($model->price ?? 0);
                        if ($unit <= 0) {
                            return response()->json([
                                'message' => 'Prix de build manquant.',
                                'errors'  => ["items.$idx.id" => ['Build sans prix']]
                            ], 422);
                        }
                        $snap = [
                            'type'        => 'build',
                            'name'        => $model->name,
                            'description' => $model->description,
                            'img_url'     => $model->img_url,
                        ];
                        $lineItems[] = [
                            'type'     => Build::class,
                            'model'    => $model,
                            'qty'      => $it['qty'],
                            'unit'     => $unit,
                            'snapshot' => $snap,
                        ];
                    } else {
                        $model = Component::query()->with(['brand','type'])->findOrFail($it['id']);
                        $unit  = (float) ($model->price ?? 0);
                        if ($unit <= 0) {
                            return response()->json([
                                'message' => 'Prix de composant manquant.',
                                'errors'  => ["items.$idx.id" => ['Composant sans prix']]
                            ], 422);
                        }
                        $snap = [
                            'type'           => 'component',
                            'name'           => $model->name,
                            'brand'          => $model->brand->name ?? null,
                            'component_type' => $model->type->name ?? null,
                            'img_url'        => $model->img_url ?? null,
                        ];
                        $lineItems[] = [
                            'type'     => Component::class,
                            'model'    => $model,
                            'qty'      => $it['qty'],
                            'unit'     => $unit,
                            'snapshot' => $snap,
                        ];
                    }
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    return response()->json([
                        'message' => 'Item introuvable.',
                        'errors'  => ["items.$idx.id" => ['Introuvable']]
                    ], 422);
                }
            }

            foreach ($lineItems as $li) {
                $subtotal += $li['unit'] * $li['qty'];
            }

            $shipping = $subtotal >= 1000 ? 0.00 : 9.90;
            $discount = 0.00;
            $tax      = round($subtotal * 0.21, 2); // TVA 21% (simple)
            $grand    = round($subtotal + $shipping - $discount + $tax, 2);

            $order = Order::create([
                'user_id'                => $userId,
                'customer_first_name'    => $validated['customer_first_name'],
                'customer_last_name'     => $validated['customer_last_name'],
                'customer_email'         => $validated['customer_email'],
                'customer_phone'         => $validated['customer_phone'] ?? null,
                'shipping_address_line1' => $validated['shipping_address_line1'],
                'shipping_address_line2' => $validated['shipping_address_line2'] ?? null,
                'shipping_city'          => $validated['shipping_city'],
                'shipping_postal_code'   => $validated['shipping_postal_code'],
                'shipping_country'       => $validated['shipping_country'],

                'subtotal'               => $subtotal,
                'shipping_cost'          => $shipping,
                'discount_total'         => $discount,
                'tax_total'              => $tax,
                'grand_total'            => $grand,

                'status'                 => 'pending',
                'payment_method'         => 'bank_transfer',
                'payment_status'         => 'awaiting_transfer',
                'currency'               => $currency,
                'payment_deadline'       => now()->addDays(config('store.payment_deadline_days', 5)),
            ]);

            foreach ($lineItems as $li) {
                OrderItem::create([
                    'order_id'         => $order->id,
                    'purchasable_type' => $li['type'],
                    'purchasable_id'   => $li['model']->id,
                    'quantity'         => $li['qty'],
                    'unit_price'       => $li['unit'],
                    'line_total'       => round($li['unit'] * $li['qty'], 2),
                    'snapshot'         => $li['snapshot'],
                ]);
            }

            // Référence de virement + infos bancaires pour l'utilisateur
            $ref = sprintf('JT-%s-%06d', now()->format('Ymd'), $order->id);
            $order->update(['bank_reference' => $ref]);

            return response()->json([
                'order_id'       => $order->id,
                'status'         => $order->status,
                'payment_status' => $order->payment_status,
                'amounts'        => [
                    'subtotal' => (string) $order->subtotal,
                    'shipping' => (string) $order->shipping_cost,
                    'discount' => (string) $order->discount_total,
                    'tax'      => (string) $order->tax_total,
                    'grand'    => (string) $order->grand_total,
                ],
                'bank' => [
                    'holder'   => config('store.bank.holder'),
                    'iban'     => config('store.bank.iban'),
                    'bic'      => config('store.bank.bic'),
                    'ref'      => $order->bank_reference,
                    'deadline' => optional($order->payment_deadline)->toDateString(),
                    'note'     => config('store.bank.note'),
                ],
            ], 201);
        });
    }


    // Récup d’une commande
    public function show(Order $order)
    {
        $order->load('items');
        return response()->json($order);
    }
}
