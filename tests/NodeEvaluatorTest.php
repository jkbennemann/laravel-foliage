<?php

declare(strict_types=1);

use Jkbennemann\Foliage\Core\Payload\ArrayPayload;
use Jkbennemann\Foliage\Core\Payload\DateAvailabilityPayloadBase;
use Jkbennemann\Foliage\Tests\Rules\DateAvailabilityRule;
use Jkbennemann\Foliage\Tests\Rules\RuleOne;
use Jkbennemann\Foliage\Validator\ValidationDataBuilder;

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
