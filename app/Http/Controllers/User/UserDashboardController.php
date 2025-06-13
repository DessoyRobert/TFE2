<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

use App\Models\Build;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $builds = Build::with('components.component')
            ->where('user_id', $user->id)
            ->get();

        return Inertia::render('User/Dashboard', [
            'builds' => $builds,
        ]);
    }
}
