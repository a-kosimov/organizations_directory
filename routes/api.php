<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\BuildingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('organizations')->group(function () {
    Route::get('{id}', [OrganizationController::class, 'show']);
    Route::get('building/{building_id}', [OrganizationController::class, 'byBuilding']);
    Route::get('activity/{activity_id}', [OrganizationController::class, 'byActivity']);
    Route::get('nearby', [OrganizationController::class, 'nearby']);
    Route::get('search', [OrganizationController::class, 'search']);
    Route::get('activity/{activity_id}/search', [OrganizationController::class, 'searchByActivityWithDescendants']);
});


Route::prefix('buildings')->group(function () {
    Route::get('/', [BuildingController::class, 'index']);
});

