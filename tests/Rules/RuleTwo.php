<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Tests\Rules;

use Jkbennemann\Foliage\Core\BaseValidationRule;
use Jkbennemann\Foliage\Core\Payload\BaseValidationPayload;
use Jkbennemann\Foliage\Exceptions\RuleValidation;

class RuleTwo extends BaseValidationRule
{
    protected function validation(BaseValidationPayload $payload): void
    {
        if (! in_array($this->settings()['bar'], $payload->toArray(), true)) {
            throw new RuleValidation($this, 'rule 2 data mismatch');
        }
    }

    protected function key(): string
    {
        return 'rule_2';
    }

    protected function inverseValidationException(BaseValidationPayload $payload): RuleValidation
    {
        // TODO: Implement inverseValidationException() method.
    }
}
