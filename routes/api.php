<?php

use App\Http\Controllers\AvatarController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DojoController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/dojos',[DojoController::class,'index']);
Route::get('/dojos/category/{category}',[CategoryController::class,'dojos']);
Route::get('/dojos/{dojo}',[DojoController::class,'edit'])->middleware('auth:sanctum');
Route::post('/dojos',[DojoController::class,'store'])->middleware('auth:sanctum');
Route::delete('dojos/{dojo}',[DojoController::class,'destroy'])->middleware('auth:sanctum');
Route::patch('/dojos/{dojo}', [DojoController::class,'update'])->middleware('auth:sanctum');

Route::get('/categories',[CategoryController::class,'index']);
Route::post('/categories', [CategoryController::class,'store'])->middleware('auth:sanctum');
Route::delete('/categories/{category}',[CategoryController::class,'destroy'])->middleware('admin');
Route::patch('/categories/{category}/approve',[CategoryController::class,'approve'])->middleware('admin');

Route::get('/users',[UserController::class,'index'])->middleware('admin');
Route::patch('/users/{user}', [UserController::class,'update'])->middleware('admin');

Route::post('/avatar',[AvatarController::class,'store'])->middleware('auth:sanctum');