<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Tests\Payloads;

use Jkbennemann\Foliage\Core\Payload\BaseValidationPayload;

class IsAdminPayload extends BaseValidationPayload
{
    public function __construct(
        public bool $isAdmin
    ) {
    }
}
