<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Tests\Rules;

use Jkbennemann\Foliage\Core\BaseValidationRule;
use Jkbennemann\Foliage\Core\Payload\BaseValidationPayload;
use Jkbennemann\Foliage\Exceptions\RuleValidation;
use Jkbennemann\Foliage\Tests\Payloads\AmountPayload;

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
