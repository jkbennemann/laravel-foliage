<?php

declare(strict_types=1);

use Jkbennemann\Foliage\Core\Node;
use Jkbennemann\Foliage\Core\Payload\ArrayPayload;
use Jkbennemann\Foliage\Core\Rule as RuleAlias;
use Jkbennemann\Foliage\Core\RuleParser;
use Jkbennemann\Foliage\Core\TreeBuilder;
use Jkbennemann\Foliage\Exceptions\RuleValidation;
use Jkbennemann\Foliage\Facades\Rule;
use Jkbennemann\Foliage\Tests\Rules\RuleOne;
use Jkbennemann\Foliage\Tests\Rules\RuleTwo;

it('can build an empty tree structure', function () {
    $rule = Rule::empty();

    expect($rule)
        ->toBeInstanceOf(RuleAlias::class)
        ->and($rule->node())
        ->toBeInstanceOf(Node::class)
        ->and($rule->node()->isEmpty())
        ->toBeTrue();
});

it('can build a simple tree structure with a single rule', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
    ]);
    $rule = Rule::single(RuleOne::class, ['foo' => 'bar']);
    $ruleData = $rule->toArray();

    expect($rule)
        ->toBeInstanceOf(RuleAlias::class)
        ->and($rule->node())
        ->toBeInstanceOf(Node::class)
        ->and($ruleData)
        ->toMatchArray([
            'children' => [],
            'data' => [
                'foo' => 'bar',
            ],
            'name' => 'rule_1',
            'operation' => null,
            'type' => 'leaf',
        ]);
});

it('can build a simple tree structure with a negated single rule', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
    ]);
    $rule = Rule::not(RuleOne::class, ['foo' => 'bar']);
    $ruleData = $rule->toArray();

    expect($rule)
        ->toBeInstanceOf(RuleAlias::class)
        ->and($rule->node())
        ->toBeInstanceOf(Node::class)
        ->and($ruleData)
        ->toMatchArray([
            'children' => [],
            'data' => [
                'foo' => 'bar',
            ],
            'name' => 'rule_1',
            'operation' => 'NOT',
            'type' => 'leaf',
        ]);
});

it('can build a json tree structure with a single rule - syntax 1', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
    ]);
    $rule = Rule::single(RuleOne::class, ['foo' => 'bar']);
    $json = '{"alias":null,"children":[],"data":{"foo":"bar"},"name":"rule_1","operation":null,"type":"leaf"}';

    expect($rule->jsonSerialize())
        ->toBe($json);
});

it('can build a json tree structure with a single rule - syntax 2', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
    ]);
    $rule = Rule::single(RuleOne::class, new ArrayPayload([
        'foo' => 'bar',
    ]));
    $json = '{"alias":null,"children":[],"data":{"foo":"bar"},"name":"rule_1","operation":null,"type":"leaf"}';

    expect($rule->jsonSerialize())
        ->toBe($json);
});

it('can build a simple tree structure with multiple rules - syntax 1', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);
    $rule = Rule::and(
        Rule::single(RuleOne::class, ['foo' => 'bar']),
        Rule::single(RuleTwo::class, ['bar' => 'baz'])
    );
    $ruleData = $rule->toArray();

    ray(json_encode($ruleData));

    expect($rule)
        ->toBeInstanceOf(RuleAlias::class)
        ->and($rule->node())
        ->toBeInstanceOf(Node::class)
        ->and($ruleData)
        ->toMatchArray([
            'alias' => null,
            'children' => [
                [
                    'alias' => null,
                    'children' => [],
                    'data' => [
                        'foo' => 'bar',
                    ],
                    'name' => 'rule_1',
                    'operation' => null,
                    'type' => 'leaf',
                ],
                [
                    'alias' => null,
                    'children' => [],
                    'data' => [
                        'bar' => 'baz',
                    ],
                    'name' => 'rule_2',
                    'operation' => null,
                    'type' => 'leaf',
                ],
            ],
            'data' => null,
            'name' => null,
            'operation' => 'AND',
            'type' => 'node',
        ]);
});

it('can build a simple tree structure with multiple rules - syntax 2', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);
    $rule = Rule::and(
        [RuleOne::class, ['foo' => 'bar']],
        [RuleTwo::class, ['bar' => 'baz']]
    );
    $ruleData = $rule->toArray();

    expect($rule)
        ->toBeInstanceOf(RuleAlias::class)
        ->and($rule->node())
        ->toBeInstanceOf(Node::class)
        ->and($ruleData)
        ->toMatchArray([
            'alias' => null,
            'children' => [
                [
                    'alias' => null,
                    'children' => [],
                    'data' => [
                        'foo' => 'bar',
                    ],
                    'name' => 'rule_1',
                    'operation' => null,
                    'type' => 'leaf',
                ],
                [
                    'alias' => null,
                    'children' => [],
                    'data' => [
                        'bar' => 'baz',
                    ],
                    'name' => 'rule_2',
                    'operation' => null,
                    'type' => 'leaf',
                ],
            ],
            'data' => null,
            'name' => null,
            'operation' => 'AND',
            'type' => 'node',
        ]);
});

it('can build a tree structure from a json string', function () {
    $json = '{"alias":null,"children":[{"alias":null,"children":[],"data":{"foo":"bar"},"name":"rule_1","operation":null,"type":"leaf"},{"alias":null,"children":[],"data":{"bar":"baz"},"name":"rule_2","operation":null,"type":"leaf"}],"data":null,"name":null,"operation":"AND","type":"node"}';
    $builder = new TreeBuilder(
        new RuleParser([
            RuleOne::class,
            RuleTwo::class,
        ])
    );
    $tree = $builder->build(json_decode($json, true));

    expect($tree)
        ->toBeInstanceOf(Node::class)
        ->and($tree->jsonSerialize())
        ->toBe($json)
        ->and($tree->toArray())
        ->toMatchArray([
            'alias' => null,
            'children' => [
                [
                    'alias' => null,
                    'children' => [],
                    'data' => [
                        'foo' => 'bar',
                    ],
                    'name' => 'rule_1',
                    'operation' => null,
                    'type' => 'leaf',
                ],
                [
                    'alias' => null,
                    'children' => [],
                    'data' => [
                        'bar' => 'baz',
                    ],
                    'name' => 'rule_2',
                    'operation' => null,
                    'type' => 'leaf',
                ],
            ],
            'data' => null,
            'name' => null,
            'operation' => 'AND',
            'type' => 'node',
        ]);
});

it('cannot build a tree for a non existing rule', function () {
    Rule::single('not-existing', []);
})->throws(RuleValidation::class);

it('cannot build a tree for a not enabled but existing rule', function () {
    Rule::single(RuleOne::class, []);
})->throws(RuleValidation::class);
