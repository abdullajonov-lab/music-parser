<?php

use App\Http\Controllers\ParseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get("/parse", [ParseController::class, "parse"]);
