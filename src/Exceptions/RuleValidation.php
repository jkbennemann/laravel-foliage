<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Exceptions;

use Exception;
use Throwable;

class RuleValidation extends Exception
{
    public function __construct(
        string $message,
        int $statusCode = 422,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public static function unexpected(): RuleValidation
    {
        return new RuleValidation('Unexpected error during validation');
    }

    public static function notEnabled(?string $ruleName = null): RuleValidation
    {
        if (!$ruleName) {
            return new RuleValidation('Rule is not enabled');
        }

        return new RuleValidation(sprintf('Rule [%s] is not enabled', $ruleName));
    }

    final public function exceptionKey(): string
    {
        if (! empty($this->customKey())) {
            return 'rule_validation.'.$this->customKey();
        }

        return 'rule_validation.error.invalid';
    }

    protected function customKey(): string
    {
        return '';
    }
}
