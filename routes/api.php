<?php

use Illuminate\Support\Facades\Route;

// Import des controllers
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\BuildController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CpuController;
use App\Http\Controllers\GpuController;
use App\Http\Controllers\RamController;
use App\Http\Controllers\MotherboardController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\PsuController;
use App\Http\Controllers\CoolerController;
use App\Http\Controllers\CaseModelController;
use App\Http\Controllers\ImageController;

// Routes API REST (préfères ces routes pour ton front en Vue.js/Inertia)

// Composants spécifiques
Route::apiResource('cpus', CpuController::class);
Route::apiResource('gpus', GpuController::class);
Route::apiResource('rams', RamController::class);
Route::apiResource('motherboards', MotherboardController::class);
Route::apiResource('storages', StorageController::class);
Route::apiResource('psus', PsuController::class);
Route::apiResource('coolers', CoolerController::class);
Route::apiResource('case-models', CaseModelController::class);

// Table parent "générique"
Route::apiResource('components', ComponentController::class);

// Marques & catégories
Route::apiResource('brands', BrandController::class);
Route::apiResource('categories', CategoryController::class);

// Builds & relations builds <-> composants
Route::apiResource('builds', BuildController::class);

// Images
Route::apiResource('images', ImageController::class);

// (optionnel) Middleware d’authentification API pour certaines routes
// Route::middleware('auth:sanctum')->group(function () {
//     Route::apiResource('builds', BuildController::class);
//     // autres routes protégées
// });

/*
|-------------------------------------
| Exemple de points d’API accessibles :
| GET      /api/cpus
| GET      /api/cpus/{id}
| POST     /api/cpus
| PUT/PATCH /api/cpus/{id}
| DELETE   /api/cpus/{id}
| ... idem pour chaque ressource ci-dessus
|-------------------------------------
*/
