<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CavController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

Route::get('/cav-data', [CavController::class, 'index']);
Route::post('/cav-data', [CavController::class, 'store'])->middleware('auth');
Route::get('/cav-data/forecast', [CavController::class, 'forecast']);
