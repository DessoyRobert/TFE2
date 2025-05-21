<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ComponentController;
use App\Http\Controllers\CpuController;
use App\Http\Controllers\GpuController;
use App\Http\Controllers\RamController;
use App\Http\Controllers\MotherboardController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\PsuController;
use App\Http\Controllers\CoolerController;
use App\Http\Controllers\CaseModelController;

Route::resource('components', ComponentController::class);
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
