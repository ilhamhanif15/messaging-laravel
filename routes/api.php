<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\TemplateMessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function() {
    
    // Public Auth
    Route::prefix('auth')->group(function() {
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:api')->group(function() {

        // Auth
        Route::prefix('auth')->group(function() {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });

        // Customers
        Route::get('customers/all', [CustomerController::class, 'getAll']);
        Route::apiResource('customers', CustomerController::class);

        // Template Messages
        Route::get('template-messages/all', [TemplateMessageController::class, 'getAll']);
        Route::apiResource('template-messages', TemplateMessageController::class);
    });

});
