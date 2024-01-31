<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Tests\Rules;

use Jkbennemann\BusinessRequirements\Core\BaseValidationRule;
use Jkbennemann\BusinessRequirements\Core\Payload\BaseValidationPayload;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;
use Jkbennemann\BusinessRequirements\Tests\Payloads\PermissionsPayload;

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
