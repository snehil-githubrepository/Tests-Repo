<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ManagementController;

//new api routes

//Auth Routes
Route::post('register', [AuthController::class, 'register'])->middleware('auth.custom');
Route::post('login', [AuthController::class, 'login'])->middleware('auth.custom');
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth.custom');

Route::get('/profile/{id}', [AuthController::class, 'showProfile'])->middleware('auth.custom');
Route::put('/profile/{id}', [AuthController::class, 'updateProfile'])->middleware('auth.custom');
Route::delete('/profile/{id}', [AuthController::class, 'deleteProfile'])->middleware('auth.custom');

//Management Routes

Route::post('/product/store', [ManagementController::class, 'storeProduct']);
Route::put('/product/update/{id}', [ManagementController::class, 'updateProduct'])->middleware('admin');
Route::get('/product/show/{id}', [ManagementController::class, 'showProduct']);
Route::get('/products', [ManagementController::class, 'showAllProducts'])->middleware('admin');
Route::delete('/product/{id}',[ManagementController::class, 'deleteProduct'])->middleware('admin');
Route::get('/products/search', [ManagementController::class, 'searchProducts']);
