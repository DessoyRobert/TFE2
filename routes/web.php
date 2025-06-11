<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Contrôleurs publics
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\BuildController;
use App\Http\Controllers\ProfileController;

// Pages publiques
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

// Fiche détaillée composant (Inertia, public)
Route::get('/components/{component}', [ComponentController::class, 'showPage'])->name('components.show');

// Pages builds publiques (création consultation mais pas modif/suppression)
Route::resource('builds', BuildController::class)->only(['index', 'create', 'show']);

// Dashboard utilisateur
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Gestion du profil utilisateur
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Routes ADMIN PANEL (protégées par auth + is_admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'is_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard admin Inertia
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard', [
                'stats' => [
                    'users'      => \App\Models\User::count(),
                    'builds'     => \App\Models\Build::count(),
                    'components' => \App\Models\Component::count(),
                ],
            ]);
        })->name('dashboard');

        // Tu peux mettre ici des routes Inertia de gestion (CRUD, pages de formulaire, etc)
        // Exemple :
        // Route::resource('components', \App\Http\Controllers\Admin\ComponentController::class);
        // À adapter selon tes besoins côté admin panel (web, pas API)
    });

// Auth (login/register/...)
require __DIR__.'/auth.php';
