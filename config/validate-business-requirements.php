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
    | \Jkbennemann\BusinessRequirements\Core\BaseValidationRule.php class
    |
    */

    'available_rules' => [
        //add your rules here
    ],

    'payload_namespace' => 'App\Services\BusinessRequirements\Payloads',

    'rules_namespace' => 'App\Services\BusinessRequirements\Rules',

    'validator' => \Jkbennemann\BusinessRequirements\Validator\TreeValidator::class,

    'validation_data_builder' => \Jkbennemann\BusinessRequirements\Validator\ValidationDataBuilder::class,
];
