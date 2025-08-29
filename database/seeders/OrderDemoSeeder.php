<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Component;
use App\Models\Build;
use App\Services\OrderService;

class OrderDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = \App\Models\User::first();
        if (!$user) {
            $this->command->warn('Pas d’utilisateur trouvé, seed ignoré.');
            return;
        }

        // Création commande avec TOUTES les colonnes non-null -> valeurs par défaut
        $order = Order::create([
            'user_id' => $user->id,

            'customer_first_name' => 'Demo',
            'customer_last_name'  => 'User',
            'customer_email'      => 'demo@example.com',
            'customer_phone'      => '+32 470 00 00 00',

            'shipping_address_line1' => 'Rue de la Démo 1',
            'shipping_address_line2' => null,
            'shipping_city'          => 'Mons',
            'shipping_postal_code'   => '7000',
            'shipping_country'       => 'BE',

            // Montants: initialiser TOUT À 0.00 si NOT NULL en base
            'subtotal'       => 0.00,
            'shipping_cost'  => 9.90,   // tu peux mettre 0.00 si tu veux
            'discount_total' => 0.00,
            'tax_total'      => 0.00,
            'grand_total'    => 0.00,

            'status'         => 'pending',
            'payment_method' => null,
            'payment_status' => 'unpaid',
            'currency'       => 'EUR',
            'meta'           => null,
        ]);

        // Ajout item Component si dispo
        if ($component = Component::first()) {
            $price = (float)($component->price ?? 100);
            $order->items()->create([
                'purchasable_type' => Component::class,
                'purchasable_id'   => $component->id,
                'quantity'   => 1,
                'unit_price' => $price,
                'line_total' => $price,
                'snapshot'   => ['note' => 'Ajouté depuis OrderDemoSeeder'],
            ]);
        }

        // Ajout item Build si dispo
        if ($build = Build::first()) {
            $price = (float)($build->price ?? 899);
            $order->items()->create([
                'purchasable_type' => Build::class,
                'purchasable_id'   => $build->id,
                'quantity'   => 1,
                'unit_price' => $price,
                'line_total' => $price,
                'snapshot'   => ['note' => 'Build complet en démo'],
            ]);
        }

        // Recalcul des totaux (subtotal/grand_total)
        app(OrderService::class)->recalcTotals($order);

        $this->command->info("Commande #{$order->id} créée avec items et totaux recalculés.");
    }
}
