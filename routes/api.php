<?php

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

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

Route::post('/dojos',[DojoController::class,'store'])->middleware('auth');
Route::post('/categories', [CategoryController::class,'store'])->middleware('auth');
Route::delete('/categories/{category}',[CategoryController::class,'destroy'])->middleware('admin');
Route::patch('/categories/{category}/approve',[CategoryController::class,'approve'])->middleware('admin');