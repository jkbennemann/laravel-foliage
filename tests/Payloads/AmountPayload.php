<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Tests\Payloads;

use Jkbennemann\BusinessRequirements\Core\Payload\BaseValidationPayload;

class AmountPayload extends BaseValidationPayload
{
    public function __construct(
        public int $currentAmount
    ) {
    }
}
