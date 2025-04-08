<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;


Route::get('/', function () {
    return view('welcome');
});
Route::get('/chat', [ChatbotController::class, 'index']);
Route::post('/chatbot', [ChatbotController::class, 'ask']);
