<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});

Route::get('/go/{project}', [WebsiteController::class, 'click'])
    ->whereNumber('project')
    ->middleware('throttle:30,1')
    ->name('projects.click');

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->middleware('throttle:20,1')->name('projects.store');
    Route::post('/projects/{project}/generate', [ProjectController::class, 'generate'])
        ->middleware('throttle:3,10')
        ->name('projects.generate');
    Route::get('/projects/{project}/preview', [WebsiteController::class, 'preview'])->name('projects.preview');
    Route::get('/projects/{project}/export', [WebsiteController::class, 'export'])
        ->middleware('throttle:5,10')
        ->name('projects.export');
});
