<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Tests\Rules;

use Jkbennemann\BusinessRequirements\Core\BaseValidationRule;
use Jkbennemann\BusinessRequirements\Core\Payload\BaseValidationPayload;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;
use Jkbennemann\BusinessRequirements\Tests\Payloads\AmountPayload;

class MaximumAmountRule extends BaseValidationRule
{
    /**
     * @param  AmountPayload  $payload
     *
     * @throws RuleValidation
     */
    protected function validation(BaseValidationPayload $payload): void
    {
        if ($payload->currentAmount >= $this->settings()[0]) {
            throw new RuleValidation($this, 'Maximum amount exceeded!', $payload, 'maximum_exceeded');
        }
    }

    protected function key(): string
    {
        return 'maximum_amount';
    }

    protected function inverseValidationException(BaseValidationPayload $payload): RuleValidation
    {

    }

    public function payloadObjectClass(): string
    {
        return AmountPayload::class;
    }
}
