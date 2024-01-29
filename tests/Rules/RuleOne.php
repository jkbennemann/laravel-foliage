<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Tests\Rules;

use Jkbennemann\BusinessRequirements\Core\BaseValidationRule;
use Jkbennemann\BusinessRequirements\Core\Contracts\ValidationPayloadContract;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;

class RuleOne extends BaseValidationRule
{

    protected function validation(ValidationPayloadContract $payload): void
    {
        // TODO: Implement validation() method.
    }

    protected function key(): string
    {
        return 'rule_1';
    }

    protected function inverseValidationException(ValidationPayloadContract $payload): RuleValidation
    {
        // TODO: Implement inverseValidationException() method.
    }
}
