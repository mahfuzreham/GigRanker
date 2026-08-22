<?php

declare(strict_types=1);

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminHomepageController;
use App\Http\Controllers\AdminHostedSiteLinkController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WebsiteController;
use App\Services\AppSettings;
use Illuminate\Support\Facades\Route;

Route::get('/', function (AppSettings $settings) { return view('home', ['site' => $settings->home()]); })->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:6,1')->name('admin.login.store');
});

Route::get('/go/{project}', [WebsiteController::class, 'click'])->whereNumber('project')->middleware('throttle:30,1')->name('projects.click');

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');
    Route::get('/billing/plans', [BillingController::class, 'index'])->name('billing.plans');
    Route::post('/billing/select', [BillingController::class, 'select'])->middleware('throttle:10,1')->name('billing.select');
    Route::get('/billing/payment', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/billing/payment', [PaymentController::class, 'store'])->middleware('throttle:5,10')->name('payments.store');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/settings', [AdminSettingsController::class, 'edit'])->name('admin.settings');
    Route::post('/admin/settings', [AdminSettingsController::class, 'update'])->middleware('throttle:10,1')->name('admin.settings.update');
    Route::get('/admin/homepage', [AdminHomepageController::class, 'index'])->name('admin.homepage');
    Route::post('/admin/homepage', [AdminHomepageController::class, 'store'])->middleware('throttle:30,1')->name('admin.homepage.store');
    Route::put('/admin/homepage/{section}', [AdminHomepageController::class, 'update'])->middleware('throttle:30,1')->name('admin.homepage.update');
    Route::delete('/admin/homepage/{section}', [AdminHomepageController::class, 'destroy'])->name('admin.homepage.destroy');
    Route::get('/admin/hosted-sites', [AdminHostedSiteLinkController::class, 'index'])->name('admin.hosted-sites');
    Route::post('/admin/hosted-sites', [AdminHostedSiteLinkController::class, 'store'])->middleware('throttle:30,1')->name('admin.hosted-sites.store');
    Route::put('/admin/hosted-sites/{hostedSiteLink}', [AdminHostedSiteLinkController::class, 'update'])->middleware('throttle:30,1')->name('admin.hosted-sites.update');
    Route::post('/admin/hosted-sites/{hostedSiteLink}/toggle', [AdminHostedSiteLinkController::class, 'toggle'])->name('admin.hosted-sites.toggle');
    Route::delete('/admin/hosted-sites/{hostedSiteLink}', [AdminHostedSiteLinkController::class, 'destroy'])->name('admin.hosted-sites.destroy');
    Route::get('/admin/payments', [AdminPaymentController::class, 'index'])->name('admin.payments.index');
    Route::post('/admin/payments/{payment}/approve', [AdminPaymentController::class, 'approve'])->middleware('throttle:30,1')->name('admin.payments.approve');
    Route::post('/admin/payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->middleware('throttle:30,1')->name('admin.payments.reject');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->middleware('throttle:20,1')->name('projects.store');
    Route::post('/projects/{project}/generate', [ProjectController::class, 'generate'])->middleware('throttle:3,10')->name('projects.generate');
    Route::get('/projects/{project}/preview', [WebsiteController::class, 'preview'])->name('projects.preview');
    Route::get('/projects/{project}/export', [WebsiteController::class, 'export'])->middleware('throttle:5,10')->name('projects.export');
});
