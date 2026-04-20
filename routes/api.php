<?php

declare(strict_types = 1);

use Centrex\Crm\Http\Controllers\Api\{ActivityController, ClvController, CompanyController, ContactController, DealController, LeadController};
use Illuminate\Support\Facades\Route;

Route::middleware(config('crm.api_middleware', ['api', 'auth:sanctum']))
    ->prefix(config('crm.api_prefix', 'api/crm'))
    ->name('crm.api.')
    ->group(function (): void {
        Route::apiResource('companies', CompanyController::class);

        Route::apiResource('contacts', ContactController::class);

        Route::apiResource('leads', LeadController::class);
        Route::post('leads/{lead}/qualify', [LeadController::class, 'qualify'])->name('leads.qualify');
        Route::post('leads/{lead}/mark-lost', [LeadController::class, 'markLost'])->name('leads.mark-lost');

        Route::apiResource('deals', DealController::class);
        Route::post('deals/{deal}/advance', [DealController::class, 'advance'])->name('deals.advance');

        Route::apiResource('activities', ActivityController::class);
        Route::post('activities/{activity}/complete', [ActivityController::class, 'complete'])->name('activities.complete');

        Route::get('contacts/{contact}/clv', [ClvController::class, 'show'])->name('contacts.clv.show');
        Route::post('contacts/{contact}/clv/recalculate', [ClvController::class, 'recalculate'])->name('contacts.clv.recalculate');
        Route::get('clv/leaderboard', [ClvController::class, 'leaderboard'])->name('clv.leaderboard');
        Route::post('clv/batch-recalculate', [ClvController::class, 'batchRecalculate'])->name('clv.batch-recalculate');
    });
