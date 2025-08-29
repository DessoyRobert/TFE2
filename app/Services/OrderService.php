<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // Crée l’order vide (totaux à 0 au départ)
            $order = Order::create([
                'user_id' => $data['user_id'],
                'customer_first_name' => $data['customer_first_name'] ?? null,
                'customer_last_name'  => $data['customer_last_name'] ?? null,
                'customer_email'      => $data['customer_email'] ?? null,
                'customer_phone'      => $data['customer_phone'] ?? null,
                'shipping_address_line1' => $data['shipping_address_line1'] ?? null,
                'shipping_address_line2' => $data['shipping_address_line2'] ?? null,
                'shipping_city'          => $data['shipping_city'] ?? null,
                'shipping_postal_code'   => $data['shipping_postal_code'] ?? null,
                'shipping_country'       => $data['shipping_country'] ?? null,
                'subtotal'       => 0,
                'shipping_cost'  => (float)($data['shipping_cost'] ?? 0),
                'discount_total' => (float)($data['discount_total'] ?? 0),
                'tax_total'      => (float)($data['tax_total'] ?? 0),
                'grand_total'    => 0,
                'status'         => $data['status'] ?? 'pending',
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => $data['payment_status'] ?? 'unpaid',
                'currency'       => $data['currency'] ?? 'EUR',
                'meta'           => $data['meta'] ?? null,
            ]);

            // Items (optionnel à la création)
            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $it) {
                    $qty  = max(1, (int)($it['quantity'] ?? 1));
                    $unit = (float)($it['unit_price'] ?? 0);
                    $order->items()->create([
                        'purchasable_type' => $it['purchasable_type'], // App\Models\Build ou App\Models\Component
                        'purchasable_id'   => (int)$it['purchasable_id'],
                        'quantity'   => $qty,
                        'unit_price' => $unit,
                        'line_total' => $unit * $qty,
                        'snapshot'   => $it['snapshot'] ?? null, // snapshot produit/build
                    ]);
                }
            }

            $this->recalcTotals($order);

            return $order->fresh(['items','payments']);
        });
    }

    public function addItem(Order $order, array $item): Order
    {
        $qty  = max(1, (int)($item['quantity'] ?? 1));
        $unit = (float)($item['unit_price'] ?? 0);

        $order->items()->create([
            'purchasable_type' => $item['purchasable_type'],
            'purchasable_id'   => (int)$item['purchasable_id'],
            'quantity'   => $qty,
            'unit_price' => $unit,
            'line_total' => $unit * $qty,
            'snapshot'   => $item['snapshot'] ?? null,
        ]);

        $this->recalcTotals($order);

        return $order->fresh(['items','payments']);
    }

    public function removeItem(Order $order, int $orderItemId): Order
    {
        $order->items()->whereKey($orderItemId)->delete();
        $this->recalcTotals($order);
        return $order->fresh(['items','payments']);
    }

    public function recalcTotals(Order $order): void
    {
        $order->loadMissing('items');
        $subtotal = $order->items->sum('line_total');

        $discount   = (float)($order->discount_total ?? 0);
        $shipping   = (float)($order->shipping_cost ?? 0);
        $tax        = (float)($order->tax_total ?? 0);

        $order->subtotal    = $subtotal;
        $order->grand_total = max(0, $subtotal - $discount + $shipping + $tax);
        $order->save();
    }

    public function updateStatus(Order $order, string $status): Order
    {
        $order->status = $status;
        $order->save();
        return $order->fresh(['items','payments']);
    }

    public function markPaid(Order $order, array $paymentData): Order
    {
        $order->payments()->create([
            'provider'      => $paymentData['provider'] ?? 'manual',
            'provider_ref'  => $paymentData['provider_ref'] ?? null,
            'amount'        => $paymentData['amount'] ?? $order->grand_total,
            'currency'      => $paymentData['currency'] ?? $order->currency ?? 'EUR',
            'status'        => $paymentData['status'] ?? 'succeeded',
            'payload'       => $paymentData['payload'] ?? null,
        ]);

        $order->payment_status = 'paid';
        $order->status = $order->status === 'pending' ? 'processing' : $order->status;
        $order->save();

        return $order->fresh(['items','payments']);
    }
}
