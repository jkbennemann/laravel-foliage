<?php

declare(strict_types=1);

use Jkbennemann\Foliage\Facades\Rule;
use Jkbennemann\Foliage\Tests\Rules\RuleOne;
use Jkbennemann\Foliage\Tests\Rules\RuleTwo;
use Jkbennemann\Foliage\Validator\Normalizer;

it('can normalize an empty rule', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
    ]);

    $rule = Rule::empty();

    $normalizer = new Normalizer();
    $node = $normalizer->normalize($rule);
    $treeData = $node->toArray();
    $totalRules = $node->node()->rulesFlattened()->count();

    expect($treeData)
        ->toHaveCount(6)
        ->and($treeData['children'])
        ->toHaveCount(0)
        ->and($treeData['type'])
        ->toBe('node')
        ->and($treeData['operation'])
        ->toBe(null)
        ->and($treeData['data'])
        ->toBe(null)
        ->and($treeData['name'])
        ->toBe(null)
        ->and($totalRules)
        ->toBe(0);
});

it('can normalize a single rule', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
    ]);

    $rule = Rule::single(RuleOne::class);

    $normalizer = new Normalizer();
    $node = $normalizer->normalize($rule);
    $treeData = $node->toArray();
    $totalRules = $node->node()->rulesFlattened()->count();

    expect($treeData)
        ->toHaveCount(6)
        ->and($treeData['children'])
        ->toHaveCount(0)
        ->and($treeData['type'])
        ->toBe('leaf')
        ->and($treeData['operation'])
        ->toBe(null)
        ->and($treeData['data'])
        ->toBe([])
        ->and($treeData['name'])
        ->toBe('rule_1')
        ->and($totalRules)
        ->toBe(1);
});

it('can normalize a single not rule', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
    ]);

    $rule = Rule::not(RuleOne::class);

    $normalizer = new Normalizer();
    $node = $normalizer->normalize($rule);
    $treeData = $node->toArray();
    $totalRules = $node->node()->rulesFlattened()->count();

    expect($treeData)
        ->toHaveCount(6)
        ->and($treeData['children'])
        ->toHaveCount(0)
        ->and($treeData['type'])
        ->toBe('leaf')
        ->and($treeData['operation'])
        ->toBe('NOT')
        ->and($treeData['data'])
        ->toBe([])
        ->and($treeData['name'])
        ->toBe('rule_1')
        ->and($totalRules)
        ->toBe(1);
});

it('can normalize a conjunction rule with two single rules', function () {
    config()->set('foliage.available_rules', [
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
    $totalRules = $node->node()->rulesFlattened()->count();

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
        ->toBe(null)
        ->and($totalRules)
        ->toBe(2);
});

it('can normalize a conjunction rule with three single rules', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $rule = Rule::or(
        Rule::single(RuleOne::class, ['foo' => 'bar'])->alias('test'),
        Rule::single(RuleTwo::class),
        Rule::single(RuleTwo::class),
    );

    $normalizer = new Normalizer();
    $node = $normalizer->normalize($rule);
    $treeData = $node->toArray();
    $totalRules = $node->node()->rulesFlattened()->count();

    expect($treeData)
        ->toHaveCount(6)
        ->and($treeData['children'])
        ->toHaveCount(2)
        ->and($treeData['type'])
        ->toBe('node')
        ->and($treeData['operation'])
        ->toBe('OR')
        ->and($treeData['data'])
        ->toBe(null)
        ->and($treeData['name'])
        ->toBe(null)
        ->and($totalRules)
        ->toBe(3);
});

it('can normalize a conjunction rule with all combination of rules', function () {
    config()->set('foliage.available_rules', [
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
    $totalRules = $node->node()->rulesFlattened()->count();

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
        ->toBe(null)
        ->and($totalRules)
        ->toBe(12);
});
