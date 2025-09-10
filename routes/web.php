<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Pages publiques (Inertia)
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\BuildController;

// Espace utilisateur
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\UserDashboardController;

// API pour SPA (session web + auth)
use App\Http\Controllers\Api\BuildController as ApiBuildController;
use App\Http\Controllers\Api\CheckoutController as ApiCheckoutController;

// Admin Panel
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ComponentController as AdminComponentController;
use App\Http\Controllers\Admin\BuildController as AdminBuildController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ComponentTypeController as AdminComponentTypeController;
use App\Http\Controllers\Admin\CompatibilityRuleController as AdminCompatibilityRuleController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

// Commandes côté public/utilisateur
use App\Http\Controllers\OrderController as PublicOrderController;
use App\Http\Controllers\User\UserOrderController;

/*
|--------------------------------------------------------------------------
| Routes PUBLIQUES (pages)
|--------------------------------------------------------------------------
*/
Route::get('/', [BuildController::class, 'index'])->name('builds.index');

Route::get('/components', [ComponentController::class, 'indexPage'])->name('components.index');
Route::get('/components/{component}/details', [ComponentController::class, 'showDetailPage'])->name('components.details');

// Builds (pages Inertia)
Route::resource('builds', BuildController::class)->only(['index', 'create', 'show', 'store']);

/*
|--------------------------------------------------------------------------
| Espace UTILISATEUR (auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // Page Checkout (Inertia)
    Route::get('/checkout', fn () => Inertia::render('Checkout/Index'))->name('checkout.index');

    // Page détail commande (hydrate Checkout/Index avec serverResult)
    Route::get('/checkout/{order}', [PublicOrderController::class, 'show'])->name('checkout.show');

    // Liste des commandes de l'utilisateur
    Route::get('/account/orders', [UserOrderController::class, 'index'])->name('account.orders.index');
});

// Gestion du profil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| API pour SPA (même domaine) — protégée par session web + auth
| → Ces routes sont appelées par le front Vue/Inertia (POST/PUT) et lisent la session.
|--------------------------------------------------------------------------
*/
Route::prefix('api')->middleware(['web', 'auth'])->group(function () {
    // Checkout (création de commande) — endpoint utilisé par Checkout/Index.vue
    Route::post('/checkout', [ApiCheckoutController::class, 'store'])->name('api.checkout.store');

    // Build (actions d’écriture)
    Route::post('/builds', [ApiBuildController::class, 'store'])->name('api.builds.store');
    Route::put('/builds/{build}', [ApiBuildController::class, 'update'])->name('api.builds.update');
});

/*
|--------------------------------------------------------------------------
| ADMIN PANEL (auth + is_admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'is_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Commandes (Inertia + JSON utilitaires)
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/list', [AdminOrderController::class, 'list'])->name('orders.list'); // avant {order}
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::post('/orders', [AdminOrderController::class, 'store'])->name('orders.store');
        Route::post('/orders/{order}/items', [AdminOrderController::class, 'addItem'])->name('orders.items.add');
        Route::delete('/orders/{order}/items/{orderItemId}', [AdminOrderController::class, 'removeItem'])->name('orders.items.remove');
        Route::post('/orders/{order}/mark-paid', [AdminOrderController::class, 'markPaid'])->name('orders.markPaid');

        // Ressources admin (CRUD)
        Route::resource('components', AdminComponentController::class);
        Route::resource('builds', AdminBuildController::class);
        Route::resource('users', AdminUserController::class);
        Route::resource('brands', AdminBrandController::class);
        Route::resource('categories', AdminCategoryController::class);
        Route::resource('component-types', AdminComponentTypeController::class);
        Route::resource('compatibility-rules', AdminCompatibilityRuleController::class);

        // Upload images
        Route::get('/images/upload', [ImageController::class, 'uploadPage'])->name('images.upload');
        Route::post('/images', [ImageController::class, 'store'])->name('images.store');
        Route::get('/images', [ImageController::class, 'index'])->name('images.index');

        // Visibility toggle pour les builds
        Route::patch('builds/{build}/visibility', [AdminBuildController::class, 'toggleVisibility'])
            ->name('builds.toggle-visibility');
    });

// Auth scaffolding (Breeze/Jetstream/etc.)
require __DIR__ . '/auth.php';
