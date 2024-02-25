<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Validator\Contracts;

use Jkbennemann\Foliage\Core\BaseValidationRule;
use Jkbennemann\Foliage\Core\Payload\BaseValidationPayload;

interface ValidationDataContract
{
    public function build(BaseValidationRule|string $rule, array $payload, ?string $alias = null): BaseValidationPayload;
}
