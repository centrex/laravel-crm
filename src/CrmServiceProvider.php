<?php

declare(strict_types = 1);

namespace Centrex\Crm;

use Centrex\Crm\Commands\{CalculateClvCommand, CrmCommand, ScoreLeadsCommand};
use Centrex\Crm\Services\ClvCalculator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CrmServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'crm');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ((bool) config('crm.web_enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }

        if ((bool) config('crm.api_enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }

        $this->registerGates();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('crm.php'),
            ], 'laravel-crm-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'laravel-crm-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/crm'),
            ], 'laravel-crm-views');

            $this->commands([
                CrmCommand::class,
                CalculateClvCommand::class,
                ScoreLeadsCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'crm');

        $this->app->singleton(ClvCalculator::class, fn (): ClvCalculator => new ClvCalculator());

        $this->app->singleton('crm', fn (): Crm => new Crm($this->app->make(ClvCalculator::class)));
        $this->app->singleton(Crm::class, fn (): Crm => new Crm($this->app->make(ClvCalculator::class)));
    }

    protected function registerGates(): void
    {
        $abilities = [
            'crm.dashboard.view',
            'crm.leads.view',
            'crm.leads.manage',
            'crm.deals.view',
            'crm.deals.manage',
            'crm.contacts.view',
            'crm.contacts.manage',
            'crm.companies.view',
            'crm.companies.manage',
            'crm.activities.view',
            'crm.activities.manage',
            'crm.clv.view',
        ];

        foreach ($abilities as $ability) {
            if (!Gate::has($ability)) {
                Gate::define($ability, static function ($user): bool {
                    if (Gate::has('crm-admin') && Gate::forUser($user)->check('crm-admin')) {
                        return true;
                    }

                    $roleAttribute = config('crm.admin_role_attribute');

                    if ($roleAttribute && method_exists($user, 'hasRole')) {
                        return $user->hasRole(config('crm.admin_roles', []));
                    }

                    return false;
                });
            }
        }
    }
}
