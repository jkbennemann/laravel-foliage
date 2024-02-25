# Laravel Foliage 
This packages allows you to validate arbitrary business requirements within your application.

![Laravel Foliage Logo](public/foliage-logo.png)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jkbennemann/laravel-foliage.svg?style=flat-square)](https://packagist.org/packages/jkbennemann/laravel-foliage)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jkbennemann/laravel-validate-business-requirements/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jkbennemann/laravel-foliage/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jkbennemann/laravel-validate-business-requirements/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jkbennemann/laravel-foliage/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jkbennemann/laravel-foliage.svg?style=flat-square)](https://packagist.org/packages/jkbennemann/laravel-foliage)

## What can be validated?

You can basically validate anything as long as you can make a logical expression out of it. 

Examples:
- Extend Laravel Gates/Policies with extensive validations based on your business rules
- Validate rules stored on your database models, eg. Coupon Code availability, Subscription validations, ..
- Perform any other arbitrary data validation within your classes based on e.g. request input, ...
- ...

*Your options are basically endless*

## Installation

You can install the package via composer:

```bash
composer require jkbennemann/laravel-foliage
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="foliage-config"
```

This is the contents of the published config file:

```php
return [
    'available_rules' => [
        //add your rules here
        //SampleRule::class,
    ],

    'rule_parser' => \Jkbennemann\Foliage\Core\RuleParser::class,

    'payload_namespace' => 'App\Services\Foliage\Payloads',

    'rules_namespace' => 'App\Services\Foliage\Rules',

    'validator' => \Jkbennemann\Foliage\Validator\TreeValidator::class,

    'validation_data_builder' => \Jkbennemann\Foliage\Validator\ValidationDataBuilder::class,
];
```

## How it works

The whole concept of this package is to enable arbitrary validations of business rules.  
To achieve this we will create a binary tree from your set of rules.  

The resulting tree can then be validated by a given input of data.

For each rule the required data to validate against will automatically be taken from the provided payload.

Each tree node later will be expressed as an array, containing the following data.
```php
[
    'alias' => null,
    'children' => [
        //sub nodes    
    ],
    'data' => [
        //data of your rule
    ],
    'name' => 'rule_key',
    'operation' => 'AND|OR|NOT|null',
    'type' => 'node|leaf',
]
```


## Usage

### Instantiating new rules

To create new you can either use the class provided by the package or just make use of the Facade for an easy and expressive API. 

#### Basic instantiation 
```php
$availableRules = config('foliage.available_rules');

$ruleParser = new \Jkbennemann\Foliage\Core\RuleParser($availableRules);

$foliage = new Jkbennemann\Foliage\Core\Rule($ruleParser);

$rule = $foliage->single(SampleRule::class, ['sample' => 'data']);
$rule = $foliage->and([
    [SampleRule::class, ['sample' => 'data']),
    [AnotherRule::class, ['another_sample' => 'data']),
]);
$rule = $foliage->or([
    [SampleRule::class, ['sample' => 'data']),
    [AnotherRule::class, ['another_sample' => 'data']),
]);
```
#### Facade usage
```php
use Jkbennemann\Foliage\Facades\Rule;

//Single rule usage
$rule = Rule::single(
    SampleRule::class, ['sample' => 'data']
);

$rule = Rule::not(
    SampleRule::class, ['sample' => 'data']
);

//Multi rule usage
$rule = Rule::and(
    Rule::single(SampleRule::class, ['sample' => 'data']),
    Rule::not(AnotherRule::class, ['another_sample' => 'data']),
);

$rule = Rule::or(
    Rule::single(SampleRule::class, ['sample' => 'data']),
    Rule::not(AnotherRule::class, ['another_sample' => 'data']),
    Rule::and(
        Rule::not(ThirdRule::class, ['another_sample' => 'data']),
        Rule::not(ThirdRule::class, ['another_sample' => 'data']),
    )
);
```

A sample representation of a simple `and` rule will look like this
```php
$rule = Rule::and(
    Rule::single(SampleRule::class, ['sample' => 'data']),
    Rule::not(AnotherRule::class, ['another_sample' => 'data']),
);
$structure = $rule->toArray();

echo $rule->jsonSerialize();
```
```json
{
    "alias": null,
    "children":
    [
        {
            "alias": null,
            "children":
            [],
            "data":
            {
                "sample": "data"
            },
            "name": "sample_rule",
            "operation": null,
            "type": "leaf"
        },
        {
            "alias": null,
            "children":
            [],
            "data":
            {
                "another_sample": "data"
            },
            "name": "another_rule",
            "operation": null,
            "type": "leaf"
        }
    ],
    "data": null,
    "name": null,
    "operation": "AND",
    "type": "node"
}
```

### Use Aliases for rule payload

If you want to use the same rule multiple times, with different options you will need to specify an alias to the rule.  
A alias basically overrides the argument name for the payload to validate against.

Assuming you as an administrator want to perform an action on behalf of a user of your application.  
You as the administrator have the right to do so, but the user under consideration itself has not.

```php
$payload = [
    'user_is_admin' => $user->isAdmin(),
    'performing_user_is_admin' => $currentLoggedInUser->isAdmin(),
];

$rule = Rule::or(
    Rule::single(IsAdminRule::class)->alias('performing_user_is_admin'),
    Rule::and(
        Rule::not(IsAdminRule::class)->alias('user_is_admin'),
        Rule::not(IsAllowedUser::class, ['user' => 'allowed_user']),
    ),
);
```

### Creating rule by structured tree data

Eventually you want to store the tree structure inside your database to validate against eloquent models.  

In this case you model should implement the `HasValidationRules` trait provided by the package.  
This gives you access to those rules.  

Your database field should be a `json` field if you're using MySQL/MariaDB.

```php
use Jkbennemann\Foliage\Core\HasValidationRules;

class CouponCode extends Model {

    use HasValidationRules;
    
    protected $casts = [
        'database_field_name' => 'array',
    ];

    //..
    
    protected function rulesFieldName(): string
    {
        return 'database_field_name';
    }
}
```

Your model now has access to
```php
$node = $coupon->validationNode();  //returns a Node object for validation
```

#### From existing array

If you have an array, already in the tree structure, you can create a node from it like so
```php
$ruleData = [
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
]

$builder = app(\Jkbennemann\Foliage\Core\TreeBuilder::class);
$node = $builder->build($ruleData);
```

### Validating rules

To validate a created set of rules you can
- invoke the `validate()` method of the Foliage class
- create your own validator
- call the `validate()` method on a `Node` instance

```php
$rule = Rule::single(SampleRule::class, ['name' => 'John Doe']);
$node = $rule->node();

//payload constructed during your application's request lifecycle.
$payload = [
    'name' => 'John Doe'
];

//manual instantiation
//returns `Result` object
$foliage = new Foliage($validator, $treeBuilder);
$result = $foliage->validateSilently($node, $payload);  // does not throw an exception on error
$foliage->validate($node, $payload);                    // throws exception on error

//using Facade
//returns `Result` object
$result = \Jkbennemann\Foliage\Facades\Foliage::validate($node, $payload)
$result->isValid();  //true
$result->errors();   //empty collection
```

#### Exception Handling

By default, the validator throws an exception on first occurring validation error.  

If you want to change this behaviour you can instruct the validator not to raise an exception.
```php
$validator = new TreeValidator(new ValidationDataBuilder(), new PostOrderEvaluator());
$validator->withoutExceptions();

$isValid = $validator->evaluate($node, $payload);
$errors = $validator->errors();

$validator->evaluate($node, $payload)
```

### Container resolution

The package makes use of the Laravel container, by taking the settings from the config file `config/foliage.php`

Because of this you can also instantiate a validator by calling the container.

```php
use Jkbennemann\Foliage\Validator\Contracts\BaseValidator;

$validator = app(BaseValidator::class);
$validator->withoutExceptions();
$validator->withExceptions();
```

### Create a new Validation Rule

If you want to create a new rule, you can run the `artisan` command.
```
php artisan validation:create-rule SampleRule
```

This command creates a new rule within the namespace, specified inside the config file.  
The content will be 

```php
<?php

declare(strict_types=1);

namespace App\Services\BusinessRequirements\Rules;

use Jkbennemann\Foliage\Core\BaseValidationRule;
use Jkbennemann\Foliage\Core\Payload\BaseValidationPayload;
use Jkbennemann\Foliage\Exceptions\RuleValidation;

class SampleRule extends BaseValidationRule
{
    /** @throws RuleValidation */
    protected function validation(BaseValidationPayload $payload): void
    {
        //your implementation
    }

    protected function key(): string
    {
        return 'sample';
    }

    protected function inverseValidationException(BaseValidationPayload $payload): RuleValidation
    {
        throw new RuleValidation($this, 'error_message', $payload, 'custom_key');
    }
}
```

### Create a new Payload Class

If you want to create a payload class that can be used for a specific rule, you can run the `artisan` command.
```
php artisan validation:create-payload AvailabilityPayload
```

This command creates a new payload within the namespace, specified inside the config file.  
The content will be

```php
<?php

declare(strict_types=1);

namespace App\Services\BusinessRequirements\Payloads;

use Jkbennemann\Foliage\Core\Payload\BaseValidationPayload;

class AvailabilityPayload extends BaseValidationPayload
{
    public function __construct(
    ) {
    }
}
```

You can add any arguments to the constructor you like, e.g:
```php
public function __construct(public \Illuminate\Support\Carbon $date) {}
```

Now inside of you validation rule, you can override the used payload class for this rule as follows:
```php
class SampleRule extends BaseValidationRule
{
    /**
    * @param AvailabilityPayload $payload 
    * @throws RuleValidation 
    */
    protected function validation(BaseValidationPayload $payload): void
    {
        $ruleSettings = $this->settings();
        $dateNeeded = $ruleSettings['until'];

        if ($payload->date->lt($dateNeeded)) {
            return;
        }
        
        throw new \Jkbennemann\Foliage\Exceptions\RuleValidation($this, 'Not available', $payload);
    }
    
    //..
    
    public function payloadObjectClass(): string
    {
        return AvailabilityPayload::class;
    }
}
```

To construct the rule you can now validate your business logic like this:
```php
$payload = [
    'date' => now(),
];

$rule = Rule::single(SampleRule::class, ['until' => Carbon::make('01-02-2024')])

$result = Foliage::validateSilently($rule->node(), $payload);
$result->isValid();
$result->errors();
```
## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jakob Bennemann](https://github.com/jkbennemann)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
