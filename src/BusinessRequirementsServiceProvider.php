<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements;

use Jkbennemann\BusinessRequirements\Commands\BusinessRequirementsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BusinessRequirementsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-validate-business-requirements')
            ->hasConfigFile()
            ->hasCommand(BusinessRequirementsCommand::class);
    }
}
