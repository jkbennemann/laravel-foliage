<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements;

use Jkbennemann\BusinessRequirements\Commands\BusinessRequirementsCommand;
use Jkbennemann\BusinessRequirements\Validator\Contracts\Validator;
use Jkbennemann\BusinessRequirements\Validator\TreeValidator;
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
        $this->app->bind(Validator::class, function () {
            return new TreeValidator();
        });
    }
}
