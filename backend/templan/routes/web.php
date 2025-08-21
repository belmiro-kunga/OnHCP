<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

// Redirecionar para o frontend Vue.js
Route::get('/', function () {
    return redirect('http://localhost:5173');
});

// API routes para o sistema OnHCP
Route::prefix('api')->group(function () {
    // Rotas da API serão definidas aqui
    Route::get('/health', function () {
        return response()->json(['status' => 'OK', 'message' => 'OnHCP API is running']);
    });
});

// Broadcasting routes
Broadcast::routes();
