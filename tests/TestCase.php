<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jkbennemann\BusinessRequirements\BusinessRequirementsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Jkbennemann\\BusinessRequirements\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            BusinessRequirementsServiceProvider::class,
            LaravelDataServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-validate-business-requirements_table.php.stub';
        $migration->up();
        */
    }
}
