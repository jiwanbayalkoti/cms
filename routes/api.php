<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Bill Modules API
Route::middleware('auth:sanctum')->prefix('projects')->group(function () {
    Route::get('{projectId}/bills', [\App\Http\Controllers\Api\BillModuleController::class, 'index']);
    Route::post('{projectId}/bills', [\App\Http\Controllers\Api\BillModuleController::class, 'store']);
});

Route::middleware('auth:sanctum')->prefix('bills')->group(function () {
    Route::get('{id}', [\App\Http\Controllers\Api\BillModuleController::class, 'show']);
    Route::put('{id}', [\App\Http\Controllers\Api\BillModuleController::class, 'update']);
    Route::post('{id}/approve', [\App\Http\Controllers\Api\BillModuleController::class, 'approve']);
    Route::post('{billId}/items/{itemId}/measure', [\App\Http\Controllers\Api\BillModuleController::class, 'addMeasurement']);
    Route::get('{id}/export/excel', [\App\Http\Controllers\Api\BillModuleController::class, 'exportExcel']);
    Route::get('{id}/export/pdf', [\App\Http\Controllers\Api\BillModuleController::class, 'exportPdf']);
    Route::get('{id}/history', [\App\Http\Controllers\Api\BillModuleController::class, 'history']);
});
