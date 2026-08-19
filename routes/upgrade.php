<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/upgrade-to-v1-2-0', function () {
    abort_unless(config('app.upgrade_mode'), 404);

    Artisan::call('migrate', ['--force' => true]);
});

Route::get('/upgrade/database', function () {
    abort_unless(config('app.upgrade_mode'), 404);

    Artisan::call('migrate', ['--force' => true]);
});
