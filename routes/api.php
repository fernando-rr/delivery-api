<?php

use App\Http\Controllers\Api\Central\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Delivery API',
        'version' => '1.0.0',
    ]);
});

Route::prefix('central')->group(function () {
    Route::post('restaurants', [RestaurantController::class, 'store']);
});
