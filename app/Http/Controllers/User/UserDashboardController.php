<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Build;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Dashboard utilisateur : liste des builds créés.
 *
 * Objectif :
 * - Afficher un prix fiable (pris en BDD, snapshot déjà calculé à la création).
 * - Pas besoin d'eager-load les composants juste pour le dashboard.
 */
class UserDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $builds = Build::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get([
                'id',
                'name',
                'description',
                'total_price', // <- snapshot en BDD
                'price',       // <- fallback legacy (anciens builds)
            ]);

        return Inertia::render('User/Dashboard', [
            'builds' => $builds,
        ]);
    }
}
