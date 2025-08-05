<?php

use App\Http\Controllers\API\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('localization')->group(function () {
    Route::prefix('auth')->controller(AuthController::class)->name('auth.')->group(function () {
        Route::post('/authenticate', 'authenticate')->name('authenticate');
        Route::post('/register', 'register')->name('register');

        // Routes protected by access token
        Route::middleware(['jwt.access', 'auth:api'])->group(function () {
            Route::get('me', 'me')->name('me');           // Fetch authenticated user details
            Route::delete('revoke', 'revoke')->name('revoke'); // Revoke the user's token
        });

        // Route protected by refresh token
        Route::middleware(['jwt.refresh', 'auth:api'])->group(function () {
            Route::post('refresh', 'refresh')->name('refresh');  // Refresh access token
        });
    });
});
