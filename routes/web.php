<?php

declare(strict_types = 1);

use Centrex\Crm\Crm;
use Illuminate\Support\Facades\Route;

Route::middleware(config('crm.web_middleware', ['web', 'auth']))
    ->prefix(config('crm.web_prefix', 'crm'))
    ->name('crm.')
    ->group(function (): void {
        Route::get('/', function (Crm $crm) {
            return view('crm::dashboard', [
                'summary'            => $crm->getPipelineSummary(),
                'dealsByStage'       => $crm->dealsByStage(),
                'upcomingActivities' => $crm->upcomingActivities(),
            ]);
        })->name('dashboard');
    });
