<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Models\Build;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(PlaceOrderRequest $request): JsonResponse
    {
        $buildId = (int) $request->input('build_id');

        $build = Build::with(['components' => function ($q) {
            $q->select('components.id', 'components.name', 'components.price');
        }])->findOrFail($buildId);

        $incoming = collect($request->input('component_ids', []))->filter()->values();
        $componentIds = $incoming->isEmpty()
            ? $build->components->pluck('id')
            : $incoming->intersect($build->components->pluck('id'))->values();

        if ($componentIds->isEmpty()) {
            return response()->json([
                'message' => 'Le build ne contient aucun composant.',
                'errors'  => ['component_ids' => ['Aucun composant valide trouvé pour ce build.']],
            ], 422);
        }

        return DB::transaction(function () use ($build, $componentIds) {
            $items    = $build->components->whereIn('id', $componentIds);
            $subtotal = $items->sum('price');
            $shipping = 0.00;
            $tax      = 0.00;
            $total    = $subtotal + $shipping + $tax;

            $order = Order::create([
                'user_id'        => auth()->id(),
                'status'         => 'pending',
                'payment_status' => 'unpaid',
                'currency'       => 'EUR',
                'subtotal'       => $subtotal,
                'shipping_total' => $shipping,
                'tax_total'      => $tax,
                'grand_total'    => $total,
                'build_id'       => $build->id,
            ]);

            foreach ($items as $component) {
                OrderItem::create([
                    'order_id'     => $order->id,
                    'component_id' => $component->id,
                    'name'         => $component->name,
                    'unit_price'   => $component->price,
                    'quantity'     => 1,
                    'total'        => $component->price,
                ]);
            }

            return response()->json([
                'message'      => 'Commande créée.',
                'order_id'     => $order->id,
                'redirect_url' => route('checkout.show', ['order' => $order->id]),
            ], 201);
        });
    }
}
