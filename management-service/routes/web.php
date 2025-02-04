<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/create-short-url', [UrlController::class, 'createShortURL']);
Route::delete('/delete-url/{id}', [UrlController::class, 'deleteURL']);
