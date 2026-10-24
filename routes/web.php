<?php

use App\Http\Controllers\LinkController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LinkController::class, 'index'])->name('links.index');
Route::post('/links', [LinkController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('links.store');
Route::get('/links/{link}', [LinkController::class, 'show'])->name('links.show');

// Siempre al final, para que no se coma las otras rutas
Route::get('/{code}', RedirectController::class)
    ->where('code', '[A-Za-z0-9]{6}')
    ->name('links.redirect');
