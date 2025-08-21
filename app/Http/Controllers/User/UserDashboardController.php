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

        $builds = Build::query()
            ->where('user_id', $user->id)
            ->with([
                // Relations RÉELLES sur Component
                'components.brand',
                'components.type',
                // (optionnel) si tu veux précharger les sous-modèles spécialisés
                'components.cpu',
                'components.gpu',
                'components.ram',
                'components.motherboard',
                'components.storage',
                'components.psu',
                'components.cooler',
                'components.casemodel',
                // (optionnel) images polymorphes
                // 'components.images',
            ])
            ->get();

        return Inertia::render('User/Dashboard', [
            'builds' => $builds,
        ]);
    }
}
