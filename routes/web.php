<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



use App\Http\Controllers\ComponentController;
use App\Http\Controllers\BuildController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;

Route::resource('builds', App\Http\Controllers\BuildController::class);
Route::resource('components', App\Http\Controllers\ComponentController::class);
Route::resource('brands', App\Http\Controllers\BrandController::class);
Route::resource('categories', App\Http\Controllers\CategoryController::class);
/*
Route::resource('cpus', CpuController::class);
Route::resource('gpus', GpuController::class);
Route::resource('rams', RamController::class);
Route::resource('motherboards', MotherboardController::class);
Route::resource('storages', StorageController::class);
Route::resource('psus', PsuController::class);
Route::resource('coolers', CoolerController::class);
Route::resource('cases', CaseModelController::class);
*/
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
