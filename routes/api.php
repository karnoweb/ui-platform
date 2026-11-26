<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use KarnoWeb\TenantUi\Http\Controllers\RuntimePageController;
use KarnoWeb\TenantUi\Http\Controllers\RuntimeResolveController;

Route::prefix(config('tenant-ui.runtime_api.prefix', 'api/ui'))
    ->middleware(config('tenant-ui.runtime_api.middleware', ['api']))
    ->group(function (): void {
        Route::get('/pages/{pageKey}', RuntimePageController::class)
            ->name('tenant-ui.runtime.page');

        Route::get('/resolve', RuntimeResolveController::class)
            ->name('tenant-ui.runtime.resolve');
    });
