<?php

use App\Http\Controllers\AnswerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// Routes publiques accessibles sans authentification

    // Routes pour l'authentification
    Route::post('register', [\App\Http\Controllers\AuthController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);

    // Routes pour les questions publiques
    Route::get('questions', [\App\Http\Controllers\QuestionController::class, 'index']);
    Route::get('questions/{question}', [\App\Http\Controllers\QuestionController::class, 'show']);
    Route::get('questions/{question}/answers', [\App\Http\Controllers\AnswerController::class, 'index']);

    // Routes pour les tags publiques
    Route::get('tags', [\App\Http\Controllers\TagController::class, 'index']);
Route::post('tags', [\App\Http\Controllers\TagController::class, 'store']);


// Routes nécessitant une authentification
Route::group(['middleware' => 'auth:sanctum'], function () {
    // Routes pour l'authentification
    Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout']);
    Route::post('refresh', [\App\Http\Controllers\AuthController::class, 'refresh']);

    // Routes pour les questions
    Route::post('questions', [\App\Http\Controllers\QuestionController::class, 'store']);
    Route::put('questions/{question}', [\App\Http\Controllers\QuestionController::class, 'update']);
    Route::delete('questions/{question}', [\App\Http\Controllers\QuestionController::class, 'destroy']);

    // Routes pour les réponses
    Route::post('questions/{question}/answers', [\App\Http\Controllers\AnswerController::class, 'store']);
    Route::put('answers/{answer}', [\App\Http\Controllers\AnswerController::class, 'update']);
    Route::delete('answers/{answer}', [\App\Http\Controllers\AnswerController::class, 'destroy']);


    // Routes pour les tags
    Route::put('tags/{tag}', [\App\Http\Controllers\TagController::class, 'update']);
    Route::delete('tags/{tag}', [\App\Http\Controllers\TagController::class, 'destroy']);
});
Route::middleware(['auth:sanctum', 'role:superviseur'])->put('answers/{answer}/approve', [\App\Http\Controllers\AnswerController::class, 'approve']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
