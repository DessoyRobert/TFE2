<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Inertia\Inertia;

// Contrôleurs publics
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\BuildController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\UserDashboardController;

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
Route::get('/components/add/{component}', [ComponentController::class, 'showPage'])->name('components.show');

// Builds publics : consultation/ajout uniquement
Route::resource('builds', BuildController::class)->only(['index', 'create', 'show']);

// Dashboard utilisateur

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
});



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
        // Dashboard admin via contrôleur dédié
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
         Route::resource('components', \App\Http\Controllers\Admin\ComponentController::class);
         Route::resource('builds', \App\Http\Controllers\Admin\BuildController::class);
         Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
         Route::resource('brands', \App\Http\Controllers\Admin\BrandController::class);
         Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
         route::resource('component-types', \App\Http\Controllers\Admin\ComponentTypeController::class);
    });

// Auth (login/register/...)
require __DIR__.'/auth.php';
