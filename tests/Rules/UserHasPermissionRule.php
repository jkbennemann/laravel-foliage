<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Tests\Rules;

use Jkbennemann\Foliage\Core\BaseValidationRule;
use Jkbennemann\Foliage\Core\Payload\BaseValidationPayload;
use Jkbennemann\Foliage\Exceptions\RuleValidation;
use Jkbennemann\Foliage\Tests\Payloads\PermissionsPayload;

class UserHasPermissionRule extends BaseValidationRule
{
    /**
     * @param  PermissionsPayload  $payload
     *
     * @throws RuleValidation
     */
    protected function validation(BaseValidationPayload $payload): void
    {
        foreach ($this->settings() as $neededPermission) {
            if (! in_array($neededPermission, $payload->permissions)) {
                throw new RuleValidation($this, 'No permission', $payload, 'invalid_permissions');
            }
        }
    }

    protected function key(): string
    {
        return 'user_has_permission';
    }

    public function payloadObjectClass(): string
    {
        return PermissionsPayload::class;
    }

    protected function inverseValidationException(BaseValidationPayload $payload): RuleValidation
    {
        throw new RuleValidation($this, 'error_message', $payload, 'custom_key');
    }
}
