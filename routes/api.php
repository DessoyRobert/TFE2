<?php

use Illuminate\Support\Facades\Route;

// Contrôleurs PUBLICS (retours JSON)
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

use App\Http\Controllers\Api\BuildValidationTempController;

// ---------------------------------------------------------------------
// API PUBLIQUE EN LECTURE (stateless). Aucune route nécessitant la session ici.
// ---------------------------------------------------------------------

// Composants (lecture seule)
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

// Validation/compat temporaire (publique)
Route::post('builds/validate-temp', BuildValidationTempController::class);

