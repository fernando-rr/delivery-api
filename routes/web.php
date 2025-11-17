<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Delivery WEB',
        'version' => '1.0.0',
    ]);
});
