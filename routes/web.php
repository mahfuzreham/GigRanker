<?php

declare(strict_types=1);

use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');
Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
