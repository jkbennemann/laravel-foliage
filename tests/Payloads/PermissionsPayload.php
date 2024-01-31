<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Tests\Payloads;

use Jkbennemann\BusinessRequirements\Core\Payload\BaseValidationPayload;

class PermissionsPayload extends BaseValidationPayload
{
    public function __construct(
        public array $permissions
    ) {
    }
}
