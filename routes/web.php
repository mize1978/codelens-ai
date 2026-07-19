<?php

use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/',                          [ReviewController::class, 'index'])->name('reviews.index');
Route::post('/reviews',                  [ReviewController::class, 'store'])->name('reviews.store');
Route::get('/reviews/{review}',          [ReviewController::class, 'show'])->name('reviews.show');
Route::get('/reviews/{review}/status',   [ReviewController::class, 'status'])->name('reviews.status');
Route::post('/reviews/{review}/fix',     [ReviewController::class, 'fix'])->name('reviews.fix');
Route::get('/ranking',                   [ReviewController::class, 'ranking'])->name('ranking');
