<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements;

use Jkbennemann\BusinessRequirements\Commands\BusinessRequirementsCommand;
use Jkbennemann\BusinessRequirements\Validator\Contracts\BaseValidator;
use Jkbennemann\BusinessRequirements\Validator\Contracts\ValidationDataContract;
use Jkbennemann\BusinessRequirements\Validator\TreeValidator;
use Jkbennemann\BusinessRequirements\Validator\ValidationDataBuilder;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BusinessRequirementsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-validate-business-requirements')
            ->hasConfigFile('validate-business-requirements')
            ->hasCommand(BusinessRequirementsCommand::class);
    }

    public function packageBooted(): void
    {
        $this->app->bind(ValidationDataContract::class, function ($app) {
            $class = $app['config']['validate-business-requirements']['validation_data_builder'] ?? ValidationDataBuilder::class;

            return new $class();
        });

        $this->app->bind(BaseValidator::class, function ($app) {
            return new TreeValidator($app->make(ValidationDataContract::class));
        });

    }
}
