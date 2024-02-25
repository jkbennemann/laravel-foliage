<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Tests\Rules;

use Jkbennemann\Foliage\Core\BaseValidationRule;
use Jkbennemann\Foliage\Core\Payload\BaseValidationPayload;
use Jkbennemann\Foliage\Exceptions\RuleValidation;

class RuleOne extends BaseValidationRule
{
    /** @throws RuleValidation */
    protected function validation(BaseValidationPayload $payload): void
    {
        $settings = $this->settings()['foo'];
        $validateAgainst = $payload->toArray();
        if (! in_array($settings, $validateAgainst, true)) {
            throw new RuleValidation($this, 'rule 1 data mismatch', $payload);
        }
    }

    protected function key(): string
    {
        return 'rule_1';
    }

    protected function inverseValidationException(BaseValidationPayload $payload): RuleValidation
    {
        throw new RuleValidation($this, 'data matches but should not', $payload);
    }
}
