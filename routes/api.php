<?php

use App\Http\Controllers\ChatbotProxyController;
use Illuminate\Support\Facades\Route;

Route::get('/csrf', [ChatbotProxyController::class, 'usage']);
Route::post('/csrf', [ChatbotProxyController::class, 'issueCsrf']);
Route::get('/proxy', [ChatbotProxyController::class, 'usage']);
Route::post('/proxy', [ChatbotProxyController::class, 'proxy']);
