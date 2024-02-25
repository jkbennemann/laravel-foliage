<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Tests\Rules;

use Jkbennemann\Foliage\Core\BaseValidationRule;
use Jkbennemann\Foliage\Core\Payload\BaseValidationPayload;
use Jkbennemann\Foliage\Core\Payload\DateAvailabilityPayloadBase;
use Jkbennemann\Foliage\Exceptions\RuleValidation;

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
