<?php

use App\Http\Controllers\Documents\DocumentController;
use App\Http\Controllers\Documents\DocumentShareController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/documents')->name('dashboard');

    Route::resource('documents', DocumentController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::post('documents/{document}/duplicate', [DocumentController::class, 'duplicate'])
        ->name('documents.duplicate');

    // Sharing reveals whether an email has an account, so it is rate limited against enumeration.
    Route::post('documents/{document}/shares', [DocumentShareController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('documents.shares.store');
    Route::delete('documents/{document}/shares/{share}', [DocumentShareController::class, 'destroy'])
        ->name('documents.shares.destroy');
});

require __DIR__.'/settings.php';
