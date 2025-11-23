<?php

use App\Http\Controllers\Api\Central\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::prefix('central')->group(function () {
    Route::post('restaurants', [RestaurantController::class, 'store']);
});

// The root /api path is not wrapped in 'tenant' middleware by default in bootstrap/app.php
// But here we are defining routes that will be prefixed with 'api' by Laravel automatically.
// The routes inside this group use 'tenant' middleware.
Route::middleware('tenant')->group(function () {
    Route::get('/', function () {
        return response()->json([
            'message' => 'Tenant connected',
            'tenant' => request('tenant'),
            'database' => config('database.connections.tenant.database'),
        ]);
    });
});
