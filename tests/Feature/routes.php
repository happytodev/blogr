<?php

use Happytodev\Blogr\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

// Blog routes for testing
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
