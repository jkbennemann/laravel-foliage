<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Exceptions;

use Exception;
use Jkbennemann\BusinessRequirements\Core\BaseValidationRule;
use Jkbennemann\BusinessRequirements\Core\Contracts\ValidationPayloadContract;
use Throwable;

class RuleValidation extends Exception
{
    public function __construct(
        private readonly ?BaseValidationRule $rule,
        string $message,
        private readonly ?ValidationPayloadContract $payload = null,
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
        $data = $this->payload?->getData();

        if ($data) {
            return $data;
        }

        return [];
    }

    public static function unexpected(?BaseValidationRule $rule = null): RuleValidation
    {
        return new RuleValidation($rule, 'Unexpected error during validation');
    }

    public static function notEnabled(?BaseValidationRule $rule = null): RuleValidation
    {
        if (!$rule) {
            return new RuleValidation($rule, 'Rule is not enabled');
        }

        return new RuleValidation($rule, sprintf('Rule [%s] is not enabled', $rule->normalizedKey()));
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
