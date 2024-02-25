<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Tests\Rules;

use Jkbennemann\BusinessRequirements\Core\BaseValidationRule;
use Jkbennemann\BusinessRequirements\Core\Payload\BaseValidationPayload;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;

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
