<?php

namespace Tests;

namespace Djurovicigoor\LaraFiles\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use DjurovicIgoor\LaraFiles\LaraFileServiceProvider;
use Djurovicigoor\LaraFiles\Tests\TestSupport\TestModels\TestModel;

abstract class TestCase extends Orchestra
{
    protected TestModel $testModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->testModel = TestModel::first();
    }

    /**
     * @return class-string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaraFileServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function setUpDatabase(Application $app): void
    {
        $app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->timestamps();
        });

        TestModel::create(['name' => 'test']);
    }
}
