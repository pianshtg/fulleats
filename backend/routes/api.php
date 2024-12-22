<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MyRestaurantController;
use App\Http\Controllers\MyUserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RestaurantController;
use App\Http\Middleware\JwtCheck;
use App\Http\Middleware\JwtParse;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

// Health check endpoint
Route::get('/health', function () {
    return response()->json(['message' => 'health OK!']);
});

// Routes for "my" namespace (e.g., My Restaurant, My User)
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

// Routes for "order" namespace
Route::prefix('order')->group(function () {
    Route::middleware(['auth.jwt'])->group(function() {
        Route::get('/', [OrderController::class, 'getMyOrders']);
        Route::post('/checkout/create-checkout-session', [OrderController::class, 'createCheckoutSession']);
    });
});

// Routes for "restaurant" namespace
Route::prefix('restaurant')->group(function () {
    Route::get('/{restaurantId}', [RestaurantController::class, 'getRestaurant']);
    Route::get('/search/{city}', [RestaurantController::class, 'searchRestaurant']);
});

// MongoDB and Cloudinary Test

Route::get('/test-mongo', function () {
    try {
        DB::connection('mongodb')->getPdo();  // Try to get the connection
        return 'Connected to MongoDB successfully!';
    } catch (\Exception $e) {
        return 'Connection to MongoDB failed: ' . $e->getMessage();
    }
});

Route::get('/test-cloudinary', function () {
    try {
        // Test fetching Cloudinary resources
        $result = Cloudinary::getApi()->resources(['max_results' => 1]);

        // Return the result to check if it's successful
        return response()->json([
            'status' => 'success',
            'message' => 'Cloudinary connected successfully!',
            'data' => $result
        ]);
    } catch (\Exception $e) {
        // Catch any errors and return them
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

Route::get('/test-env', function () {
    return response()->json([
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key' => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET')
    ]);
});

Route::get('/test-config', function () {
    return config('cloudinary');
});






