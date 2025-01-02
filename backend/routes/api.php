<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MyRestaurantController;
use App\Http\Controllers\MyUserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RestaurantController;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

// Health check endpoint
Route::get('/health', function () {
    return response()->json(['message' => 'health OK!']);
});

Route::prefix('my')->group(function () {
    Route::middleware(['auth.jwt'])->group(function() {
        Route::prefix('restaurant')->group(function () {
            Route::get('/', [MyRestaurantController::class, 'getMyRestaurant']);
            Route::get('/order', [MyRestaurantController::class, 'getMyRestaurantOrders']);
            Route::patch('/order/{orderId}/status', [MyRestaurantController::class, 'updateOrderStatus']);
            Route::post('/', [MyRestaurantController::class, 'createMyRestaurant']);
            Route::post('/update', [MyRestaurantController::class, 'updateMyRestaurant']);
        });
    
        Route::prefix('user')->group(function () {
            Route::post('/', [MyUserController::class, 'createCurrentUser']);
            Route::get('/', [MyUserController::class, 'getCurrentUser']);
            Route::put('/', [MyUserController::class, 'updateCurrentUser']);
        });
        
    });
});

Route::prefix('order')->group(function () {
    Route::middleware(['auth.jwt'])->group(function() {
        Route::get('/', [OrderController::class, 'getMyOrders']);
        Route::post('/checkout/create-checkout-session', [OrderController::class, 'createCheckoutSession']);
    });
});

Route::prefix('restaurant')->group(function () {
    Route::get('/{restaurantId}', [RestaurantController::class, 'getRestaurant']);
    Route::get('/search/{city}', [RestaurantController::class, 'searchRestaurant']);
});






