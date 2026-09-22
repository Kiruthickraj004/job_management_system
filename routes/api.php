<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\SavedJobController;


//auth
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::get('/jobs',[JobController::class,'index']);

Route::middleware('auth:sanctum')->group(function(){
    Route::post('logout',[AuthController::class,'logout']);
    
    // User routes - apply, save, withdraw, view saved jobs
    Route::post('/jobs/{job}/apply',[ApplicationController::class,'apply']);
    Route::post('/jobs/{job}/withdraw',[ApplicationController::class,'withdraw']);
    Route::get('/my-applications',[ApplicationController::class,'myApplications']);
    Route::get('/saved-jobs',[SavedJobController::class,'index']);
    Route::post('/jobs/{job}/save',[SavedJobController::class,'save']);
    Route::delete('/jobs/{job}/unsave',[SavedJobController::class,'unsave']);
    
    // Admin routes - job CRUD
    Route::post('/jobs',[JobController::class,'store']);
    Route::put('/jobs/{job}',[JobController::class,'update']);
    Route::delete('/jobs/{job}',[JobController::class,'delete']);
    
    // Admin only - view all applicants
    Route::get('/applicants',[ApplicationController::class,'applicants']);
});