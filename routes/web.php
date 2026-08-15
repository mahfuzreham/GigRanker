<?php

declare(strict_types=1);

use App\Http\Controllers\AdminAiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminFeatureUpdateController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\FeatureUpdateController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::get('/updates', [FeatureUpdateController::class, 'index'])->name('updates');
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});
Route::get('/go/{project}', [WebsiteController::class, 'click'])->whereNumber('project')->middleware('throttle:30,1')->name('projects.click');
Route::get('/payments/bkash/callback', [PaymentController::class, 'bkashCallback'])->middleware('throttle:30,1')->name('payments.bkash-callback');
Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');
    Route::get('/billing/plans', [BillingController::class, 'index'])->name('billing.plans');
    Route::post('/billing/select', [BillingController::class, 'select'])->middleware('throttle:10,1')->name('billing.select');
    Route::get('/billing/pay', [PaymentController::class, 'create'])->middleware('throttle:10,1')->name('payments.create');
    Route::post('/billing/payments', [PaymentController::class, 'store'])->middleware('throttle:5,10')->name('payments.store');
    Route::post('/billing/bkash/start', [PaymentController::class, 'bkashStart'])->middleware('throttle:5,10')->name('payments.bkash-start');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->middleware('throttle:20,1')->name('projects.store');
    Route::post('/projects/{project}/generate', [ProjectController::class, 'generate'])->middleware('throttle:3,10')->name('projects.generate');
    Route::get('/projects/{project}/preview', [WebsiteController::class, 'preview'])->name('projects.preview');
    Route::get('/projects/{project}/export', [WebsiteController::class, 'export'])->middleware('throttle:5,10')->name('projects.export');
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function (): void {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments');
        Route::post('/payments/{payment}/verify', [AdminPaymentController::class, 'verify'])->middleware('throttle:30,1')->name('payments.verify');
        Route::post('/payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->middleware('throttle:30,1')->name('payments.reject');
        Route::get('/settings', [AdminSettingsController::class, 'edit'])->name('settings');
        Route::post('/settings', [AdminSettingsController::class, 'update'])->middleware('throttle:10,1')->name('settings.update');
        Route::post('/settings/binance-test', [AdminSettingsController::class, 'testBinance'])->middleware('throttle:5,10')->name('settings.binance-test');
        Route::get('/ai', [AdminAiController::class, 'edit'])->name('ai');
        Route::post('/ai', [AdminAiController::class, 'update'])->middleware('throttle:10,1')->name('ai.update');
        Route::post('/ai/test', [AdminAiController::class, 'test'])->middleware('throttle:5,10')->name('ai.test');
        Route::get('/feature-updates', [AdminFeatureUpdateController::class, 'index'])->name('feature-updates');
        Route::post('/feature-updates', [AdminFeatureUpdateController::class, 'store'])->middleware('throttle:20,1')->name('feature-updates.store');
    });
});
