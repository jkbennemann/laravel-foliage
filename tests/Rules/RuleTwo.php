<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Tests\Rules;

use Jkbennemann\BusinessRequirements\Core\BaseValidationRule;
use Jkbennemann\BusinessRequirements\Core\Contracts\ValidationPayloadContract;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;

class RuleTwo extends BaseValidationRule
{
    protected function validation(ValidationPayloadContract $payload): void
    {
        if (!in_array($this->settings()['bar'], $payload->getData())) {
            throw new RuleValidation($this, 'data mismatch');
        }
    }

    protected function key(): string
    {
        return 'rule_2';
    }

    protected function inverseValidationException(ValidationPayloadContract $payload): RuleValidation
    {
        // TODO: Implement inverseValidationException() method.
    }
}
