<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Tests\Rules;

use Jkbennemann\BusinessRequirements\Core\BaseValidationRule;
use Jkbennemann\BusinessRequirements\Core\Contracts\ValidationPayloadContract;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;

class RuleOne extends BaseValidationRule
{
    /** @throws RuleValidation */
    protected function validation(ValidationPayloadContract $payload): void
    {
        if (!in_array($this->settings()['foo'], $payload->getData())) {
            throw new RuleValidation($this,'data mismatch');
        }
    }

    protected function key(): string
    {
        return 'rule_1';
    }

    protected function inverseValidationException(ValidationPayloadContract $payload): RuleValidation
    {
        throw new RuleValidation($this, 'data matches but should not');
    }
}
