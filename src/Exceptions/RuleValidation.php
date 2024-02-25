<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Exceptions;

use Exception;
use Jkbennemann\Foliage\Core\BaseValidationRule;
use Jkbennemann\Foliage\Core\Payload\BaseValidationPayload;
use Throwable;

class RuleValidation extends Exception
{
    public function __construct(
        private readonly ?BaseValidationRule $rule,
        string $message,
        private readonly ?BaseValidationPayload $payload = null,
        private readonly ?string $customKey = null,
        int $statusCode = 422,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function failedRule(): ?BaseValidationRule
    {
        return $this->rule;
    }

    public function ruleSettings(): ?array
    {
        return $this->rule?->settings();
    }

    public function payload(): array
    {
        $data = $this->payload?->toArray();

        if ($data) {
            return $data;
        }

        return [];
    }

    public static function unexpected(?BaseValidationRule $rule = null): RuleValidation
    {
        return new RuleValidation($rule, 'Unexpected error during validation', null, 'generic_error');
    }

    public static function unexpectedMismatch(?BaseValidationRule $rule = null): RuleValidation
    {
        return new RuleValidation($rule, 'Unexpected error during validation. Probably payload is not valid', null, 'generic_error_missmatch');
    }

    public static function notEnabled(?BaseValidationRule $rule = null): RuleValidation
    {
        if (! $rule) {
            return new RuleValidation($rule, 'Rule is not enabled', null, 'not_enabled');
        }

        return new RuleValidation($rule, sprintf('Rule [%s] is not enabled', $rule->normalizedKey()), null, 'not_enabled');
    }

    public static function invalidRule(string $rule): RuleValidation
    {
        return new RuleValidation(null, sprintf('Rule [%s] is invalid', $rule), null, 'invalid');
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
        return $this->customKey ?? '';
    }
}
