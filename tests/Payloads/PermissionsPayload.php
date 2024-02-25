<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Tests\Payloads;

use Jkbennemann\Foliage\Core\Payload\BaseValidationPayload;

class PermissionsPayload extends BaseValidationPayload
{
    public function __construct(
        public array $permissions
    ) {
    }
}
