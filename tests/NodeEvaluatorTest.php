<?php

declare(strict_types=1);

use Jkbennemann\BusinessRequirements\Core\Payload\ArrayPayload;
use Jkbennemann\BusinessRequirements\Core\Payload\DateAvailabilityPayloadBase;
use Jkbennemann\BusinessRequirements\Tests\Rules\DateAvailabilityRule;
use Jkbennemann\BusinessRequirements\Tests\Rules\RuleOne;
use Jkbennemann\BusinessRequirements\Validator\ValidationDataBuilder;

it('can build validation for an array payload class', function () {
    $payloadData = ['foo' => 'bar', 'bar' => 'baz'];

    $builder = new ValidationDataBuilder();
    $payload = $builder->build(RuleOne::class, $payloadData);

    expect($payload)
        ->toBeInstanceOf(ArrayPayload::class)
        ->and($payload->toArray())
        ->toMatchArray(['foo' => 'bar', 'bar' => 'baz']);

    $rule = $this->app->make(RuleOne::class);
    $rule->setSettings(['foo' => 'bar']);

    $payload = $builder->build($rule, $payloadData);

    expect($payload)
        ->toBeInstanceOf(ArrayPayload::class)
        ->and($payload->toArray())
        ->toMatchArray(['foo' => 'bar', 'bar' => 'baz']);
});

it('can build validation for an date availability payload class', function () {
    $currentTime = now();
    $payloadData = ['from' => $currentTime, 'until' => $currentTime, 'not-relevant' => 'value'];

    $builder = new ValidationDataBuilder();
    $payload = $builder->build(DateAvailabilityRule::class, $payloadData);

    expect($payload)
        ->toBeInstanceOf(DateAvailabilityPayloadBase::class)
        ->and($payload->toArray())
        ->toMatchArray(['from' => $currentTime->toIso8601String(), 'until' => $currentTime->toIso8601String()]);

    $payload = $builder->build(new DateAvailabilityRule(), $payloadData);

    expect($payload)
        ->toBeInstanceOf(DateAvailabilityPayloadBase::class)
        ->and($payload->toArray())
        ->toMatchArray(['from' => $currentTime->toIso8601String(), 'until' => $currentTime->toIso8601String()]);

    $payload = $builder->build(
        new DateAvailabilityRule(),
        ['from' => $currentTime]
    );

    expect($payload)
        ->toBeInstanceOf(DateAvailabilityPayloadBase::class)
        ->and($payload->toArray())
        ->toMatchArray(['from' => $currentTime->toIso8601String(), 'until' => null]);
});
