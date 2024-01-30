<?php

declare(strict_types=1);

use Jkbennemann\BusinessRequirements\Core\Rule;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;
use Jkbennemann\BusinessRequirements\Tests\Rules\RuleOne;
use Jkbennemann\BusinessRequirements\Validator\TreeValidator;

it('can validate a simple rule', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::single(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator();
    expect($validator->evaluate($node, ['foo' => 'bar']))
        ->toBeEmpty();
});

it('throws an exception on rule validation error', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::single(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator();
    $validator->evaluate($node, []);
})->throws(RuleValidation::class);


it('can validate a simple rule inverse rule', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::not(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator();
    expect($validator->evaluate($node, ['foo' => 'not-bar']))
        ->toBeEmpty();
});

it('throws an exception on inverse rule validation error', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::not(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator();
    $validator->evaluate($node, ['foo' => 'bar']);
})->throws(RuleValidation::class);
