<?php

use Illuminate\Support\Facades\Route;
use Laravel\Horizon\Horizon;

Route::group(['middleware' => ['web']], function () {
    Horizon::routes();
});
