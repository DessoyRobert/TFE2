<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Inertia\Inertia;

use App\Http\Controllers\ComponentController;
use App\Http\Controllers\BuildController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

// Accueil → liste des builds publics
Route::get('/', [BuildController::class, 'index'])->name('builds.index');

// Liste paginée des composants (Inertia)
Route::get('/components', [ComponentController::class, 'indexPage'])->name('components.index');

// Page de détail d’un composant
Route::get('/components/{component}/details', [ComponentController::class, 'showDetailPage'])->name('components.details');

// Builds publics : consultation/ajout
Route::resource('builds', BuildController::class)->only(['index', 'create', 'show', 'store']);

/*
|--------------------------------------------------------------------------
| Dashboard + pages réservées (auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard utilisateur
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // Checkout réservé aux utilisateurs connectés
    Route::get('/checkout', fn () => Inertia::render('Checkout/Index'))->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store'); // <- POST web
    Route::get('/orders/{order}', [CheckoutController::class, 'show'])->name('orders.show'); // <- lecture commande
});

/*
|--------------------------------------------------------------------------
| Gestion du profil utilisateur (auth)
|--------------------------------------------------------------------------
*/
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
        // Dashboard admin
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');

        Route::resource('components', \App\Http\Controllers\Admin\ComponentController::class);
        Route::resource('builds', \App\Http\Controllers\Admin\BuildController::class);
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::resource('brands', \App\Http\Controllers\Admin\BrandController::class);
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
        Route::resource('component-types', \App\Http\Controllers\Admin\ComponentTypeController::class);
        Route::resource('compatibility-rules', \App\Http\Controllers\Admin\CompatibilityRuleController::class);

        // Upload d’images
        Route::get('/images/upload', [ImageController::class, 'uploadPage'])->name('images.upload');
        Route::post('/images', [ImageController::class, 'store'])->name('images.store');
    });

// Auth (login/register/...)
require __DIR__.'/auth.php';
