<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Tests\Rules;

use Jkbennemann\BusinessRequirements\Core\BaseValidationRule;
use Jkbennemann\BusinessRequirements\Core\Payload\BaseValidationPayload;
use Jkbennemann\BusinessRequirements\Core\Payload\DateAvailabilityPayloadBase;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;

class DateAvailabilityRule extends BaseValidationRule
{
    protected function validation(BaseValidationPayload $payload): void
    {
        //
    }

    protected function key(): string
    {
        return 'rule_2';
    }

    protected function inverseValidationException(BaseValidationPayload $payload): RuleValidation
    {
        // TODO: Implement inverseValidationException() method.
    }

    public function payloadObjectClass(): string
    {
        return DateAvailabilityPayloadBase::class;
    }
}
