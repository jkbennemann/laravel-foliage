<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\BindingResolutionException;
use Jkbennemann\BusinessRequirements\Core\Node;
use Jkbennemann\BusinessRequirements\Core\Payload\ArrayPayload;
use Jkbennemann\BusinessRequirements\Core\Rule;
use Jkbennemann\BusinessRequirements\Core\TreeBuilder;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;
use Jkbennemann\BusinessRequirements\Tests\Rules\RuleOne;
use Jkbennemann\BusinessRequirements\Tests\Rules\RuleTwo;

it('can build an empty tree structure', function () {
    $rule = Rule::empty();

    expect($rule)
        ->toBeInstanceOf(Rule::class)
        ->and($rule->node())
        ->toBeInstanceOf(Node::class)
        ->and($rule->node()->isEmpty())
        ->toBeTrue();
});

it('can build a simple tree structure with a single rule', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);
    $rule = Rule::single(RuleOne::class, ['foo' => 'bar']);
    $ruleData = $rule->toArray();

    expect($rule)
        ->toBeInstanceOf(Rule::class)
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
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);
    $rule = Rule::not(RuleOne::class, ['foo' => 'bar']);
    $ruleData = $rule->toArray();

    expect($rule)
        ->toBeInstanceOf(Rule::class)
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
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);
    $rule = Rule::single(RuleOne::class, ['foo' => 'bar']);
    $json = '{"children":[],"data":{"foo":"bar"},"name":"rule_1","operation":null,"type":"leaf"}';

    expect($rule->jsonSerialize())
        ->toBe($json);
});

it('can build a json tree structure with a single rule - syntax 2', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);
    $rule = Rule::single(RuleOne::class, new ArrayPayload([
        'foo' => 'bar',
    ]));
    $json = '{"children":[],"data":{"foo":"bar"},"name":"rule_1","operation":null,"type":"leaf"}';

    expect($rule->jsonSerialize())
        ->toBe($json);
});

it('can build a simple tree structure with multiple rules - syntax 1', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);
    $rule = Rule::and(
        Rule::single(RuleOne::class, ['foo' => 'bar']),
        Rule::single(RuleTwo::class, ['bar' => 'baz'])
    );
    $ruleData = $rule->toArray();

    expect($rule)
        ->toBeInstanceOf(Rule::class)
        ->and($rule->node())
        ->toBeInstanceOf(Node::class)
        ->and($ruleData)
        ->toMatchArray([
            'children' => [
                [
                    'children' => [],
                    'data' => [
                        'foo' => 'bar',
                    ],
                    'name' => 'rule_1',
                    'operation' => null,
                    'type' => 'leaf',
                ],
                [
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
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);
    $rule = Rule::and(
        [RuleOne::class, ['foo' => 'bar']],
        [RuleTwo::class, ['bar' => 'baz']]
    );
    $ruleData = $rule->toArray();

    expect($rule)
        ->toBeInstanceOf(Rule::class)
        ->and($rule->node())
        ->toBeInstanceOf(Node::class)
        ->and($ruleData)
        ->toMatchArray([
            'children' => [
                [
                    'children' => [],
                    'data' => [
                        'foo' => 'bar',
                    ],
                    'name' => 'rule_1',
                    'operation' => null,
                    'type' => 'leaf',
                ],
                [
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
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $json = '{"children":[{"children":[],"data":{"foo":"bar"},"name":"rule_1","operation":null,"type":"leaf"},{"children":[],"data":{"bar":"baz"},"name":"rule_2","operation":null,"type":"leaf"}],"data":null,"name":null,"operation":"AND","type":"node"}';
    $builder = new TreeBuilder();
    $tree = $builder->build(json_decode($json, true));

    expect($tree)
        ->toBeInstanceOf(Node::class)
        ->and($tree->jsonSerialize())
        ->toBe($json)
        ->and($tree->toArray())
        ->toMatchArray([
            'children' => [
                [
                    'children' => [],
                    'data' => [
                        'foo' => 'bar',
                    ],
                    'name' => 'rule_1',
                    'operation' => null,
                    'type' => 'leaf',
                ],
                [
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
})->throws(BindingResolutionException::class);

it('cannot build a tree for a not enabled but existing rule', function () {
    Rule::single(RuleOne::class, []);
})->throws(RuleValidation::class);
