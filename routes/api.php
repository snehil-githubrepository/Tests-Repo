<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

//new api routes

//Auth Routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);

Route::get('/profile/{id}', [AuthController::class, 'showProfile']);
Route::put('/profile/{id}', [AuthController::class, 'updateProfile']);
Route::delete('/profile/{id}', [AuthController::class, 'deleteProfile']);

//Management Routes
// Route::get([ManagementController::class, '']);
// Route::get([ManagementController::class, '']);
// Route::get([ManagementController::class, '']);
// Route::get([ManagementController::class, '']);
// Route::get([ManagementController::class, '']);