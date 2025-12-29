<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AssetApiController;
use App\Http\Controllers\Api\ServiceRecordApiController;

Route::prefix('v1')->group(function () {

    // Asset API Routes
    Route::prefix('assets')->group(function () {
        Route::get('/', [AssetApiController::class, 'index']);
        Route::post('/', [AssetApiController::class, 'store']);
        Route::get('/{id}', [AssetApiController::class, 'show']);
        Route::put('/{id}', [AssetApiController::class, 'update']);
        Route::delete('/{id}', [AssetApiController::class, 'destroy']);
        Route::get('/status/{status}', [AssetApiController::class, 'getByStatus']);
    });

    // Service Record API Routes
    Route::prefix('service-records')->group(function () {
        Route::get('/', [ServiceRecordApiController::class, 'index']);
        Route::post('/handover', [ServiceRecordApiController::class, 'handover']);
        Route::get('/{id}', [ServiceRecordApiController::class, 'show']);
        Route::post('/{id}/pickup', [ServiceRecordApiController::class, 'pickup']);
        Route::get('/active/list', [ServiceRecordApiController::class, 'getActive']);
        Route::delete('/images/{imageId}', [ServiceRecordApiController::class, 'deleteImage']);
    });
});
