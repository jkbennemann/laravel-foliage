<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Available rules
    |--------------------------------------------------------------------------
    |
    | Add the available rules of your application here to register them.
    | Your rules must implement the
    | \Jkbennemann\Foliage\Core\BaseValidationRule.php class
    |
    */

    'available_rules' => [
        //add your rules here
    ],

    'rule_parser' => \Jkbennemann\Foliage\Core\RuleParser::class,

    'payload_namespace' => 'App\Services\Foliage\Payloads',

    'rules_namespace' => 'App\Services\Foliage\Rules',

    'validator' => \Jkbennemann\Foliage\Validator\TreeValidator::class,

    'validation_data_builder' => \Jkbennemann\Foliage\Validator\ValidationDataBuilder::class,
];
