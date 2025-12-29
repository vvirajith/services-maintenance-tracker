<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ServiceRecordController;


Route::get('/', function () {
    return redirect()->route('assets.index');
});

// Asset Routes
Route::resource('assets', AssetController::class);
Route::get('assets-by-status/{status}', [AssetController::class, 'getByStatus'])
    ->name('assets.by-status');

// Service Record Routes
Route::prefix('service-records')->name('service-records.')->group(function () {
    Route::get('/', [ServiceRecordController::class, 'index'])->name('index');
    Route::get('create', [ServiceRecordController::class, 'create'])->name('create');
    Route::post('/', [ServiceRecordController::class, 'store'])->name('store');
    Route::get('{id}', [ServiceRecordController::class, 'show'])->name('show');

    // Pickup Routes
    Route::get('{id}/pickup', [ServiceRecordController::class, 'pickupForm'])->name('pickup.form');
    Route::post('{id}/pickup', [ServiceRecordController::class, 'pickup'])->name('pickup');

    // Delete Image
    Route::delete('images/{imageId}', [ServiceRecordController::class, 'deleteImage'])->name('images.delete');
});
