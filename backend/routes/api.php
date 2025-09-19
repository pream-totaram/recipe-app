<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecipeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'authenticate'])
    ->name('login');

Route::post('/register', [AuthController::class,'register'])
    ->middleware('guest')
    ->name('register');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

   // Recipe routes
    Route::get('/recipes', [RecipeController::class, 'index']); // User's own recipes
    Route::get('/recipes/public', [RecipeController::class, 'public']); // All public recipes
    Route::get('/recipes/{recipe}', [RecipeController::class, 'show']); // Specific recipe
    Route::post('/recipes', [RecipeController::class, 'store']); // Create recipe
    Route::put('/recipes/{recipe}', [RecipeController::class, 'update']); // Update recipe
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']); // Delete recipe

    // Get recipes for a specific user
    Route::get('/users/{user}/recipes', [RecipeController::class, 'userRecipes']);
