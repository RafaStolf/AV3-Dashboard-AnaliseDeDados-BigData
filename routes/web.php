<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('throttle:120,1')->group(function () {
    Route::get('/bootstrap', [DashboardController::class, 'bootstrap']);
    Route::get('/dashboard', [DashboardController::class, 'dashboard']);
    Route::get('/export', [DashboardController::class, 'export']);
    Route::get('/content/{type}', [DashboardController::class, 'content'])->where('type', 'docs|analises');
});

Route::get('/metodologia', fn () => redirect('/docs/metodologia'));
Route::get('/{path?}', fn () => view('app'))->where('path', '(?:comparar|evolucao|distribuicao|associacao|dados|sobre|analises(?:/[a-z0-9-]+)?|docs(?:/[a-z0-9-]+){0,2})?');
