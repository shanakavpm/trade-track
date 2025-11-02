<?php

use App\Http\Controllers\TestMailController;
use Illuminate\Support\Facades\Route;

Route::get('/test-email', [TestMailController::class, 'sendTestEmail']);
