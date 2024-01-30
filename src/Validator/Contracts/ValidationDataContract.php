<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Validator\Contracts;

use Jkbennemann\BusinessRequirements\Core\BaseValidationRule;
use Jkbennemann\BusinessRequirements\Core\Payload\BaseValidationPayload;

interface ValidationDataContract
{
    public function build(BaseValidationRule|string $rule, array $payload): BaseValidationPayload;
}
