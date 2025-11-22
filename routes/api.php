<?php

use App\Http\Controllers\Api\Central\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::prefix('central')->group(function () {
    Route::post('restaurants', [RestaurantController::class, 'store']);
});

Route::middleware('tenant')->group(function () {
    Route::get('/', function () {
        return response()->json([
            'message' => 'Tenant connected',
            'tenant' => request('tenant'),
            'database' => config('database.connections.tenant.database'),
        ]);
    });
});
