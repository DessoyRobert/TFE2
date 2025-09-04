<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Build;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserDashboardController extends Controller
{
    /**
     * Dashboard utilisateur : liste des builds.
     *
     * Objectif: afficher un prix fiable même pour les builds récents.
     * - On eager-load UNIQUEMENT ce qui est nécessaire au calcul du prix
     *   (components.id + components.price) pour de bonnes perfs.
     * - Le modèle Build expose `computed_price` automatiquement.
     */
    public function index()
    {
        $user = Auth::user();

        $builds = Build::query()
            ->where('user_id', $user->id)
            ->with(['components' => function ($q) {
                // Minimal pour computed_price (inutile de charger brand/type/cpu... ici)
                $q->select('components.id', 'components.price');
            }])
            ->latest()
            ->get();

        return Inertia::render('User/Dashboard', [
            'builds' => $builds,
        ]);
    }
}
