<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('platform')->name('platform.')->group(function () {
    Route::get('login', [\App\Http\Controllers\Platform\AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [\App\Http\Controllers\Platform\AuthController::class, 'login'])->name('login.submit');

    Route::middleware(['auth', 'platform'])->group(function () {
        Route::post('logout', [\App\Http\Controllers\Platform\AuthController::class, 'logout'])->name('logout');
        Route::get('/', [\App\Http\Controllers\Platform\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('companies', \App\Http\Controllers\Platform\CompanyController::class)->except(['show', 'destroy']);
        Route::post('companies/{company}/suspend', [\App\Http\Controllers\Platform\CompanyController::class, 'toggleSuspend'])->name('companies.suspend');
        Route::resource('plans', \App\Http\Controllers\Platform\PlanController::class)->except(['show', 'destroy']);
        Route::get('branding', [\App\Http\Controllers\Platform\BrandingController::class, 'edit'])->name('branding.edit');
        Route::post('branding', [\App\Http\Controllers\Platform\BrandingController::class, 'update'])->name('branding.update');
    });
});

include 'upgrade.php';
