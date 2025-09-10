<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Models\Order;

class UserOrderController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->paginate(12)
            ->through(function (Order $o) {
                return [
                    'id'             => $o->id,
                    'created_at'     => $o->created_at->toDateTimeString(),
                    'status'         => $o->status,
                    'payment_status' => $o->payment_status,
                    'grand_total'    => (string) $o->grand_total,
                    'currency'       => $o->currency,
                ];
            });

        return Inertia::render('Account/Orders/Index', [
            'orders' => $orders,
        ]);
    }
}
