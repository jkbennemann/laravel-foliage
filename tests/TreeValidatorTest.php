<?php

declare(strict_types=1);

use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;
use Jkbennemann\BusinessRequirements\Facades\Rule;
use Jkbennemann\BusinessRequirements\Tests\Rules\MaximumAmountRule;
use Jkbennemann\BusinessRequirements\Tests\Rules\RuleOne;
use Jkbennemann\BusinessRequirements\Tests\Rules\RuleTwo;
use Jkbennemann\BusinessRequirements\Tests\Rules\UserHasPermissionRule;
use Jkbennemann\BusinessRequirements\Tests\Rules\UserIsAdminRule;
use Jkbennemann\BusinessRequirements\Validator\TreeValidator;
use Jkbennemann\BusinessRequirements\Validator\ValidationDataBuilder;

it('can validate a simple rule', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::single(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator(new ValidationDataBuilder());
    expect($validator->evaluate($node, ['foo' => 'bar']))
        ->toBeTrue();
});

it('throws an exception on rule validation error', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::single(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator(new ValidationDataBuilder());
    $validator->evaluate($node, []);
})->throws(RuleValidation::class);

it('does not throw an exception on silent rule validation error', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::single(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator(new ValidationDataBuilder());
    $validator->withoutExceptions();
    $validator->evaluate($node, []);

    expect($validator->isValid())->toBeFalse();
});

it('can validate a simple rule inverse rule', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
    ]);

    $node = Rule::not(RuleOne::class, ['foo' => 'bar'])->node();

    $validator = new TreeValidator(new ValidationDataBuilder());
    expect($validator->evaluate($node, ['foo' => 'not-bar', 'is_update' => true]))
        ->toBeTrue();
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

it('throws an exception on a conjunction rule validation error', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $node = Rule::and(
        [RuleOne::class, ['foo' => 'bar']],
        [RuleTwo::class, ['bar' => 'not-baz']],
    )->node();

    $validator = new TreeValidator(new ValidationDataBuilder());
    $validator->evaluate($node, ['foo' => 'bar', 'bar' => 'baz']);
})->throws(RuleValidation::class);

it('throws an exception on a disjunction rule validation error', function () {
    config()->set('validate-business-requirements.available_rules', [
        RuleOne::class,
        RuleTwo::class,
    ]);

    $node = Rule::or(
        [RuleOne::class, ['foo' => 'bar']],
        [RuleTwo::class, ['bar' => 'baz']],
    )->node();

    $validator = new TreeValidator(new ValidationDataBuilder());
    $validator->evaluate($node, ['foo' => 'not-bar', 'bar' => 'not-baz']);
})->throws(RuleValidation::class);

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
    expect($validator->isValid())->toBeTrue();
}); //->expectNotToPerformAssertions();

it('can validate a multi-level disjunction rule with custom payloads - violate rule 1 only', function () {
    config()->set('validate-business-requirements.available_rules', [
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

    $validator = new TreeValidator(new ValidationDataBuilder());
    $validator->evaluate($node, [
        'is_admin' => false,
        'current_amount' => 0,
        'permissions' => ['ssh.create'],
    ]);
    expect($validator->isValid())->toBeTrue();
}); //->expectNotToPerformAssertions();

it('can validate a multi-level disjunction rule with custom payloads - violate first rule of conjunction only', function () {
    config()->set('validate-business-requirements.available_rules', [
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

    $validator = new TreeValidator(new ValidationDataBuilder());
    $validator->evaluate($node, [
        'is_admin' => true,
        'current_amount' => 2,
        'permissions' => ['ssh.create'],
    ]);

    expect($validator->isValid())->toBeTrue();
}); //->expectNotToPerformAssertions();

it('can validate a multi-level disjunction rule with custom payloads - violate second rule of conjunction only', function () {
    config()->set('validate-business-requirements.available_rules', [
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

    $validator = new TreeValidator(new ValidationDataBuilder());
    $validator->evaluate($node, [
        'is_admin' => true,
        'current_amount' => 1,
        'permissions' => ['ssh.create'],
    ]);

    expect($validator->isValid())->toBeTrue();

}); //->expectNotToPerformAssertions();

it('can validate a multi-level disjunction rule with custom payloads - violate rule 1 and 2', function () {
    config()->set('validate-business-requirements.available_rules', [
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

    $validator = new TreeValidator(new ValidationDataBuilder());
    $validator->evaluate($node, [
        'is_admin' => false,
        'current_amount' => 1,
        'permissions' => [],
    ]);
})->throws(RuleValidation::class);

it('can validate a multi-level disjunction rule with custom payloads silently - violate rule 1 and 2', function () {
    config()->set('validate-business-requirements.available_rules', [
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

    $validator = new TreeValidator(new ValidationDataBuilder());
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
