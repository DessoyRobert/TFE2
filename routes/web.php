<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BuildController;
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\CpuController;
use App\Http\Controllers\GpuController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompatibilityRuleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

// Page détaillée composant
Route::get('/components/{component}', [ComponentController::class, 'showPage'])
    ->name('components.show');

// Builds : consultation publique uniquement (pas de suppression/modif en anonyme)
Route::resource('builds', BuildController::class)
     ->only(['index', 'create', 'show']);

// Page d'accueil
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

// Tableau de bord utilisateur
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
| Routes ADMIN (protégées)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'is_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard admin avec stats
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard', [
                'stats' => [
                    'users'      => \App\Models\User::count(),
                    'builds'     => \App\Models\Build::count(),
                    'components' => \App\Models\Component::count(),
                ],
            ]);
        })->name('dashboard');

        // CRUD complet pour les entités admin (hors public)
        Route::resource('brands', BrandController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('cpus', CpuController::class);
        Route::resource('gpus', GpuController::class);
        Route::resource('builds', BuildController::class)->except(['index', 'create', 'show']);
        Route::resource('users', UserController::class);
        Route::resource('compatibility-rules', CompatibilityRuleController::class);
    });

// Auth (login/register/...)
require __DIR__.'/auth.php';
