<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Tests\Payloads;

use Jkbennemann\BusinessRequirements\Core\Payload\BaseValidationPayload;

class IsAdminPayload extends BaseValidationPayload
{
    public function __construct(
        public bool $isAdmin
    ) {
    }
}
