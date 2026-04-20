<?php

declare(strict_types = 1);

namespace Centrex\Crm\Tests;

use Centrex\Crm\CrmServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Attributes\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;

#[WithWorkbench]
class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Centrex\\Crm\\Database\\Factories\\' . class_basename($modelName) . 'Factory',
        );

        $this->artisan('migrate', ['--database' => 'testing'])->run();
    }

    protected function getPackageProviders($app)
    {
        return [
            CrmServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        config()->set('crm.drivers.database.connection', 'testing');
        config()->set('crm.web_middleware', ['web']);
        config()->set('crm.api_middleware', ['api']);
        config()->set('crm.api_enabled', false);
    }
}
