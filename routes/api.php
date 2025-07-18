<?php

use Illuminate\Support\Facades\Route;

// Contrôleurs publics
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\CpuController;
use App\Http\Controllers\GpuController;
use App\Http\Controllers\RamController;
use App\Http\Controllers\MotherboardController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\PsuController;
use App\Http\Controllers\CoolerController;
use App\Http\Controllers\CaseModelController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BuildController;
use App\Http\Controllers\Api\BuildValidationTempController as BuildValidationTempController;

// Contrôleurs Admin
use App\Http\Controllers\Admin\ComponentController as AdminComponentController;
use App\Http\Controllers\Admin\CpuController as AdminCpuController;
use App\Http\Controllers\Admin\GpuController as AdminGpuController;
use App\Http\Controllers\Admin\RamController as AdminRamController;
use App\Http\Controllers\Admin\MotherboardController as AdminMotherboardController;
use App\Http\Controllers\Admin\StorageController as AdminStorageController;
use App\Http\Controllers\Admin\PsuController as AdminPsuController;
use App\Http\Controllers\Admin\CoolerController as AdminCoolerController;
use App\Http\Controllers\Admin\CaseModelController as AdminCaseModelController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\BuildController as AdminBuildController;

// -----------------------------------------------
// ROUTES PUBLIQUES OU AUTHENTIFIÉES (utilisateurs)
// -----------------------------------------------

// Lecture seule des composants
Route::get('components', [ComponentController::class, 'index']);
Route::get('components/{component}', [ComponentController::class, 'show']);
Route::get('cpus', [CpuController::class, 'index']);
Route::get('cpus/{cpu}', [CpuController::class, 'show']);
Route::get('gpus', [GpuController::class, 'index']);
Route::get('gpus/{gpu}', [GpuController::class, 'show']);
Route::get('rams', [RamController::class, 'index']);
Route::get('rams/{ram}', [RamController::class, 'show']);
Route::get('motherboards', [MotherboardController::class, 'index']);
Route::get('motherboards/{motherboard}', [MotherboardController::class, 'show']);
Route::get('storages', [StorageController::class, 'index']);
Route::get('storages/{storage}', [StorageController::class, 'show']);
Route::get('psus', [PsuController::class, 'index']);
Route::get('psus/{psu}', [PsuController::class, 'show']);
Route::get('coolers', [CoolerController::class, 'index']);
Route::get('coolers/{cooler}', [CoolerController::class, 'show']);
Route::get('case-models', [CaseModelController::class, 'index']);
Route::get('case-models/{case_model}', [CaseModelController::class, 'show']);
Route::get('brands', [BrandController::class, 'index']);
Route::get('brands/{brand}', [BrandController::class, 'show']);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);

// Builds utilisateur
Route::get('builds', [BuildController::class, 'index']);
Route::get('builds/{build}', [BuildController::class, 'show']);
Route::post('builds/validate-temp', BuildValidationTempController::class);

//
Route::post('builds', [BuildController::class, 'store']); 

// -----------------------------------------------
// ROUTES ADMIN (CRUD complet sécurisé)
// -----------------------------------------------

Route::middleware(['auth:sanctum', 'is_admin'])->prefix('admin')->group(function () {
    // Components
    Route::post('components', [AdminComponentController::class, 'store']);
    Route::put('components/{component}', [AdminComponentController::class, 'update']);
    Route::delete('components/{component}', [AdminComponentController::class, 'destroy']);

    // CPUs
    Route::post('cpus', [AdminCpuController::class, 'store']);
    Route::put('cpus/{cpu}', [AdminCpuController::class, 'update']);
    Route::delete('cpus/{cpu}', [AdminCpuController::class, 'destroy']);

    // GPUs
    Route::post('gpus', [AdminGpuController::class, 'store']);
    Route::put('gpus/{gpu}', [AdminGpuController::class, 'update']);
    Route::delete('gpus/{gpu}', [AdminGpuController::class, 'destroy']);

    // RAMs
    Route::post('rams', [AdminRamController::class, 'store']);
    Route::put('rams/{ram}', [AdminRamController::class, 'update']);
    Route::delete('rams/{ram}', [AdminRamController::class, 'destroy']);

    // Motherboards
    Route::post('motherboards', [AdminMotherboardController::class, 'store']);
    Route::put('motherboards/{motherboard}', [AdminMotherboardController::class, 'update']);
    Route::delete('motherboards/{motherboard}', [AdminMotherboardController::class, 'destroy']);

    // Storages
    Route::post('storages', [AdminStorageController::class, 'store']);
    Route::put('storages/{storage}', [AdminStorageController::class, 'update']);
    Route::delete('storages/{storage}', [AdminStorageController::class, 'destroy']);

    // PSUs
    Route::post('psus', [AdminPsuController::class, 'store']);
    Route::put('psus/{psu}', [AdminPsuController::class, 'update']);
    Route::delete('psus/{psu}', [AdminPsuController::class, 'destroy']);

    // Coolers
    Route::post('coolers', [AdminCoolerController::class, 'store']);
    Route::put('coolers/{cooler}', [AdminCoolerController::class, 'update']);
    Route::delete('coolers/{cooler}', [AdminCoolerController::class, 'destroy']);

    // Case Models
    Route::post('case-models', [AdminCaseModelController::class, 'store']);
    Route::put('case-models/{case_model}', [AdminCaseModelController::class, 'update']);
    Route::delete('case-models/{case_model}', [AdminCaseModelController::class, 'destroy']);

    // Brands
    Route::post('brands', [AdminBrandController::class, 'store']);
    Route::put('brands/{brand}', [AdminBrandController::class, 'update']);
    Route::delete('brands/{brand}', [AdminBrandController::class, 'destroy']);

    // Categories
    Route::post('categories', [AdminCategoryController::class, 'store']);
    Route::put('categories/{category}', [AdminCategoryController::class, 'update']);
    Route::delete('categories/{category}', [AdminCategoryController::class, 'destroy']);

    // Builds
    Route::post('builds', [AdminBuildController::class, 'store']);
    Route::put('builds/{build}', [AdminBuildController::class, 'update']);
    Route::delete('builds/{build}', [AdminBuildController::class, 'destroy']);
});
