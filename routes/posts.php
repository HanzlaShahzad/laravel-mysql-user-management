<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::prefix('post')
    ->middleware('sliding.window')
    ->group(function () {

        Route::get('/get', [PostController::class, 'get']);

        Route::post('/post', [PostController::class, 'post']);

        Route::delete('/delete/{id}', [PostController::class, 'delete']);

        Route::put('/update/{id}', [PostController::class, 'update']);
    });