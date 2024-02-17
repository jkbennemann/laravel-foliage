<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements;

use Illuminate\Foundation\Application;
use Jkbennemann\BusinessRequirements\Commands\CreatePayloadCommand;
use Jkbennemann\BusinessRequirements\Commands\CreateRuleCommand;
use Jkbennemann\BusinessRequirements\Core\Contracts\RuleParserContract;
use Jkbennemann\BusinessRequirements\Core\RuleParser;
use Jkbennemann\BusinessRequirements\Validator\Contracts\BaseValidator;
use Jkbennemann\BusinessRequirements\Validator\Contracts\ValidationDataContract;
use Jkbennemann\BusinessRequirements\Validator\Contracts\ValidationStrategy;
use Jkbennemann\BusinessRequirements\Validator\Strategies\SimpleEvaluator;
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
            ->hasCommands(
                CreateRuleCommand::class,
                CreatePayloadCommand::class,
            );
    }

    public function packageBooted(): void
    {
        $this->app->bind(ValidationDataContract::class, function ($app) {
            $class = $app['config']['validate-business-requirements']['validation_data_builder'] ?? ValidationDataBuilder::class;

            return new $class();
        });

        $this->app->bind(ValidationStrategy::class, function ($app) {
            return new SimpleEvaluator();
        });

        $this->app->bind(RuleParser::class, function ($app) {
            $availableRules = $app['config']['validate-business-requirements']['available_rules'] ?? [];

            return new RuleParser($availableRules);
        });

        $this->app->bind(RuleParserContract::class, function (Application $app) {
            $class = $app['config']['validate-business-requirements']['rule_parser'] ?? RuleParser::class;

            return $app->make($class);
        });

        $this->app->bind(BaseValidator::class, function ($app) {
            return new TreeValidator(
                $app->make(ValidationDataContract::class),
                $app->make(ValidationStrategy::class),
            );
        });

    }
}
