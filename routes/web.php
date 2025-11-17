<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Delivery WEB TEST',
        'version' => '1.0.0',
    ]);
});
