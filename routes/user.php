
<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('user')->group(function () {

    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);

    // Forgot and reset password (public)
    Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
    Route::post('/reset-password', [UserController::class, 'resetPassword']);

    // Email verification
    Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('verification.verify');

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [UserController::class, 'logout']);
        Route::post('/email/resend', [UserController::class, 'resendEmail']);

        // Authenticated + verified routes
        Route::middleware('verified')->group(function () {

            Route::get('/profile', [UserController::class, 'profile']);
            Route::get('/', [UserController::class, 'get']);
            Route::get('/{id}', [UserController::class, 'specificUser']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'delete']);

        });
    });
});
