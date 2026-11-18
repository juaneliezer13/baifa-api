<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'baifa-api',
        'version' => '1.0.7',
        'status' => 'ok',
    ]);
});
