<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->when($request->input('status'), fn($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')
            ->paginate(20)
            ->through(function (Order $o) {
                return [
                    'id' => $o->id,
                    'created_at' => $o->created_at->toDateTimeString(),
                    'customer' => $o->customer_name,
                    'email' => $o->customer_email,
                    'status' => $o->status,
                    'payment_status' => $o->payment_status,
                    'grand_total' => (string)$o->grand_total,
                    'currency' => $o->currency,
                ];
            });

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'status' => $request->input('status'),
            ],
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|string|in:pending,paid,preparing,shipped,delivered,canceled,refunded'
        ]);

        $order->update(['status' => $data['status']]);

        return back()->with('success', 'Statut mis à jour.');
    }
}
