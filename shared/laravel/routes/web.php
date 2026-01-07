<?php

use App\Http\Controllers\AutoloadStatisticsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('autoload')->group(function () {
    Route::get('/', [AutoloadStatisticsController::class, 'index']);
    Route::get('/detailed', [AutoloadStatisticsController::class, 'detailed']);
});