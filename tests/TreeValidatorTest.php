<?php

declare(strict_types=1);

use Jkbennemann\Foliage\Exceptions\RuleValidation;
use Jkbennemann\Foliage\Facades\Rule;
use Jkbennemann\Foliage\Tests\Rules\MaximumAmountRule;
use Jkbennemann\Foliage\Tests\Rules\RuleOne;
use Jkbennemann\Foliage\Tests\Rules\RuleTwo;
use Jkbennemann\Foliage\Tests\Rules\UserHasPermissionRule;
use Jkbennemann\Foliage\Tests\Rules\UserIsAdminRule;
use Jkbennemann\Foliage\Validator\Strategies\PostOrderEvaluator;
use Jkbennemann\Foliage\Validator\TreeValidator;
use Jkbennemann\Foliage\Validator\ValidationDataBuilder;

it('can validate an empty rule', function () {
    $node = Rule::empty()->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());

    expect($validator->evaluate($node, ['foo' => 'bar']))
        ->toBeTrue();
});

it('can validate a simple rule', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::single(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());

    expect($validator->evaluate($node, ['foo' => 'bar']))
        ->toBeTrue();
});

it('throws an exception on rule validation error', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::single(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
    $validator->evaluate($node, []);
})->throws(RuleValidation::class);

it('does not throw an exception on silent rule validation error', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::single(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
    $validator->withoutExceptions();
    $validator->evaluate($node, []);

    expect($validator->isValid())->toBeFalse();
});

it('can validate a simple rule inverse rule', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::not(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());

    expect($validator->evaluate($node, ['foo' => 'not-bar', 'is_update' => true]))
        ->toBeTrue();
});

it('throws an exception on inverse rule validation error', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::not(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
    $validator->evaluate($node, ['foo' => 'bar']);
})->throws(RuleValidation::class);

it('can validate a conjunction rule', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $node = Rule::and(
        [RuleOne::class, ['foo' => 'bar']],
        [RuleTwo::class, ['bar' => 'baz']],
    )->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
    $validator->evaluate($node, ['foo' => 'bar', 'bar' => 'baz']);
})->expectNotToPerformAssertions();

it('throws an exception on a conjunction rule validation error', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $node = Rule::and(
        [RuleOne::class, ['foo' => 'bar']],
        [RuleTwo::class, ['bar' => 'not-baz']],
    )->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
    $validator->evaluate($node, ['foo' => 'bar', 'bar' => 'baz']);
})->throws(RuleValidation::class);

it('throws an exception on a disjunction rule validation error', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $node = Rule::or(
        [RuleOne::class, ['foo' => 'bar']],
        [RuleTwo::class, ['bar' => 'baz']],
    )->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
    $validator->evaluate($node, ['foo' => 'not-bar', 'bar' => 'not-baz']);
})->throws(RuleValidation::class);

it('can validate a disjunction rule', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $node = Rule::or(
        [RuleOne::class, ['foo' => 'bar']],
        [RuleTwo::class, ['bar' => 'baz']],
    )->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
    $validator->evaluate($node, ['foo' => 'not-bar', 'bar' => 'baz']);
})->expectNotToPerformAssertions();

it('can validate a multi-level disjunction rule', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $rule = Rule::or(
        Rule::single(RuleOne::class, ['foo' => 'bar']),
        Rule::and(
            [RuleTwo::class, ['bar' => 'baz']],
            [RuleOne::class, ['foo' => 'barz']],
            [RuleOne::class, ['foo' => 'barz']],
        )
    );

    $normalizer = new \Jkbennemann\Foliage\Validator\Normalizer();
    $node = $normalizer->normalize($rule)->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
    $validator->evaluate($node, ['foo' => 'barz', 'bar' => 'baz']);
})->expectNotToPerformAssertions();

it('can validate a multi-level conjunction rule', function () {
    config()->set('foliage.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $rule = Rule::and(
        Rule::single(RuleOne::class, ['foo' => 'bar']),
        Rule::or(
            [RuleTwo::class, ['bar' => 'not-baz']],
            [RuleOne::class, ['foo' => 'baz']],
        )
    );

    $normalizer = new \Jkbennemann\Foliage\Validator\Normalizer();
    $node = $normalizer->normalize($rule)->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
    $validator->evaluate($node, ['foo' => 'bar', 'bar' => 'not-baz']);

    expect($validator->isValid())->toBeTrue();
});

it('can validate a multi-level disjunction rule with custom payloads - violate rule 1 only', function () {
    config()->set('foliage.available_rules', [
        UserIsAdminRule::class,
        UserHasPermissionRule::class,
        MaximumAmountRule::class,
    ]);

    $node = Rule::or(
        Rule::single(UserIsAdminRule::class, []),
        Rule::and(
            [UserHasPermissionRule::class, ['ssh.create']],
            [MaximumAmountRule::class, [1]],
        )
    )->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
    $validator->evaluate($node, [
        'is_admin' => false,
        'current_amount' => 0,
        'permissions' => ['ssh.create'],
    ]);
    expect($validator->isValid())->toBeTrue();
});

it('can validate a multi-level disjunction rule with custom payloads - violate first rule of conjunction only', function () {
    config()->set('foliage.available_rules', [
        UserIsAdminRule::class,
        UserHasPermissionRule::class,
        MaximumAmountRule::class,
    ]);

    $node = Rule::or(
        Rule::single(UserIsAdminRule::class, []),
        Rule::and(
            [UserHasPermissionRule::class, ['ssh.create']],
            [MaximumAmountRule::class, [1]],
        )
    );

    $normalizer = new \Jkbennemann\Foliage\Validator\Normalizer();
    $node = $normalizer->normalize($node)->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
    $validator->evaluate($node, [
        'is_admin' => true,
        'current_amount' => 2,
        'permissions' => ['ssh.create'],
    ]);

    expect($validator->isValid())->toBeTrue();
});

it('can validate a multi-level disjunction rule with custom payloads - violate second rule of conjunction only', function () {
    config()->set('foliage.available_rules', [
        UserIsAdminRule::class,
        UserHasPermissionRule::class,
        MaximumAmountRule::class,
    ]);

    $node = Rule::or(
        Rule::single(UserIsAdminRule::class, []),
        Rule::and(
            [UserHasPermissionRule::class, ['ssh.create']],
            [MaximumAmountRule::class, [1]],
        )
    )->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
    $validator->evaluate($node, [
        'is_admin' => true,
        'current_amount' => 1,
        'permissions' => ['ssh.create'],
    ]);

    expect($validator->isValid())->toBeTrue();
});

it('can validate a multi-level disjunction rule with custom payloads - violate rule 1 and 2', function () {
    config()->set('foliage.available_rules', [
        UserIsAdminRule::class,
        UserHasPermissionRule::class,
        MaximumAmountRule::class,
    ]);

    $node = Rule::or(
        Rule::single(UserIsAdminRule::class, []),
        Rule::and(
            [UserHasPermissionRule::class, ['ssh.create']],
            [MaximumAmountRule::class, [1]],
        )
    )->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
    $validator->evaluate($node, [
        'is_admin' => false,
        'current_amount' => 1,
        'permissions' => [],
    ]);
})->throws(RuleValidation::class);

it('can validate a multi-level disjunction rule with custom payloads silently - violate rule 1 and 2', function () {
    config()->set('foliage.available_rules', [
        UserIsAdminRule::class,
        UserHasPermissionRule::class,
        MaximumAmountRule::class,
    ]);

    $node = Rule::or(
        Rule::single(UserIsAdminRule::class, []),
        Rule::and(
            [UserHasPermissionRule::class, ['ssh.create']],
            [MaximumAmountRule::class, [1]],
        )
    )->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
    $validator->withoutExceptions();
    $validator->evaluate($node, [
        'is_admin' => false,
        'current_amount' => 1,
        'permissions' => [],
    ]);

    expect($validator->isValid())
        ->toBeFalse()
        ->and($validator->errors())
        ->toHaveCount(3);
});

it('can validate permissions with aliased payload', function () {
    config()->set('foliage.available_rules', [
        UserHasPermissionRule::class,
        UserIsAdminRule::class,
    ]);

    $payload = [
        'permissions' => ['permission_1'],
        'permissions_user_2' => ['permission_2'],
        'is_admin' => true,
    ];

    $node = Rule::and(
        Rule::single(UserHasPermissionRule::class, ['permission_1']),
        Rule::and(
            Rule::single(UserIsAdminRule::class, []),
            Rule::single(UserHasPermissionRule::class, ['permission_2'])->alias('permissions_user_2'),
        )
    )->node();

    $validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
    $validator->evaluate($node, $payload);
})->expectNotToPerformAssertions();
