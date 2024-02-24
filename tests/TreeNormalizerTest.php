<?php

declare(strict_types=1);

use Jkbennemann\BusinessRequirements\Facades\Rule;
use Jkbennemann\BusinessRequirements\Validator\Normalizer;
use Jkbennemann\BusinessRequirements\Tests\Rules\RuleOne;
use Jkbennemann\BusinessRequirements\Tests\Rules\RuleTwo;

it('can normalize a conjunction rule with two single rules', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $rule = Rule::and(
        Rule::single(RuleOne::class),
        Rule::single(RuleTwo::class),
    );

    $normalizer = new Normalizer();
    $node = $normalizer->normalize($rule);
    $treeData = $node->toArray();

    expect($treeData)
        ->toHaveCount(6)
        ->and($treeData['children'])
        ->toHaveCount(2)
        ->and($treeData['type'])
        ->toBe('node')
        ->and($treeData['operation'])
        ->toBe('AND')
        ->and($treeData['data'])
        ->toBe(null)
        ->and($treeData['name'])
        ->toBe(null);
});

it('can normalize a conjunction rule with three single rules', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $rule = Rule::and(
        Rule::single(RuleOne::class, ['foo' => 'bar'])->alias('test'),
        Rule::single(RuleTwo::class),
        Rule::single(RuleTwo::class),
        Rule::single(RuleTwo::class),
        Rule::not(RuleTwo::class),
        Rule::or(
            Rule::not(RuleOne::class),
            Rule::single(RuleOne::class),
        ),
        Rule::or(
            Rule::single(RuleOne::class),
            Rule::single(RuleOne::class),
        ),
        Rule::and(
            Rule::single(RuleOne::class),
            Rule::single(RuleOne::class),
        ),
        Rule::single(RuleTwo::class),
    );

    $normalizer = new Normalizer();
    $node = $normalizer->normalize($rule);
    $treeData = $node->toArray();

    expect($treeData)
        ->toHaveCount(6)
        ->and($treeData['children'])
        ->toHaveCount(2)
        ->and($treeData['type'])
        ->toBe('node')
        ->and($treeData['operation'])
        ->toBe('AND')
        ->and($treeData['data'])
        ->toBe(null)
        ->and($treeData['name'])
        ->toBe(null);
});
