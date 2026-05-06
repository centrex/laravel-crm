<?php

declare(strict_types = 1);

use Centrex\Crm\Crm;
use Centrex\Crm\Http\Controllers\{EmailSettingsController, WhatsappController};
use Illuminate\Support\Facades\Route;

Route::middleware(config('crm.web_middleware', ['web', 'auth']))
    ->prefix(config('crm.web_prefix', 'crm'))
    ->name('crm.')
    ->group(function (): void {
        Route::get('/', function (Crm $crm) {
            return view('crm::dashboard', [
                'summary'            => $crm->getPipelineSummary(),
                'dealsByStage'       => $crm->dealsByStage(),
                'upcomingActivities' => $crm->upcomingActivities(8),
                'overdueActivities'  => $crm->getOverdueActivities(),
                'conversionRates'    => $crm->getConversionRates(),
                'forecast'           => $crm->getRevenueForecast(3),
                'clvLeaderboard'     => $crm->getClvLeaderboard(5),
                'whatsappStats'      => $crm->getWhatsappStats(),
            ]);
        })->name('dashboard');

        Route::get('/email-settings', [EmailSettingsController::class, 'edit'])->name('email-settings.edit');
        Route::post('/email-settings', [EmailSettingsController::class, 'update'])->name('email-settings.update');

        // WhatsApp Web portal
        Route::prefix('whatsapp')->name('whatsapp.')->group(function (): void {
            Route::get('/', [WhatsappController::class, 'compose'])->name('compose');
            Route::post('/send', [WhatsappController::class, 'send'])->name('send');
            Route::get('/history', [WhatsappController::class, 'history'])->name('history');
            Route::get('/templates', [WhatsappController::class, 'templates'])->name('templates');
            Route::post('/templates', [WhatsappController::class, 'storeTemplate'])->name('templates.store');
            Route::patch('/templates/{template}/toggle', [WhatsappController::class, 'toggleTemplate'])->name('templates.toggle');
            Route::get('/open/{message}', [WhatsappController::class, 'markOpened'])->name('open');
        });
    });
