<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Models\Build;
use App\Models\Order;

class UserDashboardController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();

        $builds = Build::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(5)
            ->get(['id','name','description','price','total_price','created_at']);

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(5)
            ->get(['id','status','payment_status','grand_total','currency','created_at']);

        return Inertia::render('User/Dashboard/Index', [
            'builds' => $builds,
            'orders' => $orders,
        ]);
    }
}
