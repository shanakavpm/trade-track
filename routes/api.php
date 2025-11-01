<?php

use App\Http\Controllers\Analytics\KpiController;
use App\Http\Controllers\Payments\MockCallbackController;
use Illuminate\Support\Facades\Route;

// Payment callback (signed route)
Route::post('/payments/mock/callback/{payment}', MockCallbackController::class)
    ->name('payments.mock.callback');

// Analytics routes
Route::prefix('kpi')->group(function () {
    Route::get('/today', [KpiController::class, 'today'])->name('kpi.today');
});

Route::prefix('leaderboard')->group(function () {
    Route::get('/current-month', [KpiController::class, 'leaderboard'])->name('leaderboard.current-month');
});
