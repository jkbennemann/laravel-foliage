<?php

declare(strict_types=1);

use Jkbennemann\BusinessRequirements\Core\Rule;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;
use Jkbennemann\BusinessRequirements\Tests\Rules\RuleOne;
use Jkbennemann\BusinessRequirements\Tests\Rules\RuleTwo;
use Jkbennemann\BusinessRequirements\Validator\TreeValidator;
use Jkbennemann\BusinessRequirements\Validator\ValidationDataBuilder;

it('can validate a simple rule', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::single(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator(new ValidationDataBuilder());
    expect($validator->evaluate($node, ['foo' => 'bar']))
        ->toBeEmpty();
});

it('throws an exception on rule validation error', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::single(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator(new ValidationDataBuilder());
    $validator->evaluate($node, []);
})->throws(RuleValidation::class);

it('can validate a simple rule inverse rule', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::not(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator(new ValidationDataBuilder());
    expect($validator->evaluate($node, ['foo' => 'not-bar', 'is_update' => true]))
        ->toBeEmpty();
});

it('throws an exception on inverse rule validation error', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::not(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator(new ValidationDataBuilder());
    $validator->evaluate($node, ['foo' => 'bar']);
})->throws(RuleValidation::class);

it('can validate a conjunction rule', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $node = Rule::and(
        [RuleOne::class, ['foo' => 'bar']],
        [RuleTwo::class, ['bar' => 'baz']],
    )->node();

    $validator = new TreeValidator(new ValidationDataBuilder());
    $validator->evaluate($node, ['foo' => 'bar', 'bar' => 'baz']);
})->expectNotToPerformAssertions();

it('can validate a disjunction rule', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $node = Rule::or(
        [RuleOne::class, ['foo' => 'bar']],
        [RuleTwo::class, ['bar' => 'baz']],
    )->node();

    $validator = new TreeValidator(new ValidationDataBuilder());
    $validator->evaluate($node, ['foo' => 'not-bar', 'bar' => 'baz']);
})->expectNotToPerformAssertions();

it('can validate a multi-level disjunction rule', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $node = Rule::or(
        Rule::single(RuleOne::class, ['foo' => 'not-bar']),
        Rule::and(
            [RuleOne::class, ['foo' => 'bar']],
            [RuleTwo::class, ['bar' => 'baz']],
        )
    )->node();

    $validator = new TreeValidator(new ValidationDataBuilder());
    $validator->evaluate($node, ['foo' => 'bar', 'bar' => 'baz']);
})->expectNotToPerformAssertions();

it('can validate a multi-level conjunction rule', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $node = Rule::and(
        Rule::single(RuleOne::class, ['foo' => 'bar']),
        Rule::or(
            [RuleOne::class, ['foo' => 'bar']],
            [RuleTwo::class, ['bar' => 'not-baz']],
        )
    )->node();

    $validator = new TreeValidator(new ValidationDataBuilder());
    $validator->evaluate($node, ['foo' => 'bar', 'bar' => 'baz']);
})->expectNotToPerformAssertions();
