<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage;

use Illuminate\Foundation\Application;
use Jkbennemann\Foliage\Commands\CreatePayloadCommand;
use Jkbennemann\Foliage\Commands\CreateRuleCommand;
use Jkbennemann\Foliage\Core\Contracts\RuleParserContract;
use Jkbennemann\Foliage\Core\RuleParser;
use Jkbennemann\Foliage\Validator\Contracts\BaseValidator;
use Jkbennemann\Foliage\Validator\Contracts\ValidationDataContract;
use Jkbennemann\Foliage\Validator\Contracts\ValidationStrategy;
use Jkbennemann\Foliage\Validator\Strategies\PostOrderEvaluator;
use Jkbennemann\Foliage\Validator\TreeValidator;
use Jkbennemann\Foliage\Validator\ValidationDataBuilder;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FoliageServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-foliage')
            ->hasConfigFile('foliage')
            ->hasCommands(
                CreateRuleCommand::class,
                CreatePayloadCommand::class,
            );
    }

    public function packageBooted(): void
    {
        $this->app->bind(ValidationDataContract::class, function ($app) {
            $class = $app['config']['foliage']['validation_data_builder'] ?? ValidationDataBuilder::class;

            return new $class();
        });

        $this->app->bind(ValidationStrategy::class, function ($app) {
            return new PostOrderEvaluator();
        });

        $this->app->bind(RuleParser::class, function ($app) {
            $availableRules = $app['config']['foliage']['available_rules'] ?? [];

            return new RuleParser($availableRules);
        });

        $this->app->bind(RuleParserContract::class, function (Application $app) {
            $class = $app['config']['foliage']['rule_parser'] ?? RuleParser::class;

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
