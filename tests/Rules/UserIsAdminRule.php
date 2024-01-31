<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Tests\Rules;

use Jkbennemann\BusinessRequirements\Core\BaseValidationRule;
use Jkbennemann\BusinessRequirements\Core\Payload\BaseValidationPayload;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;
use Jkbennemann\BusinessRequirements\Tests\Payloads\IsAdminPayload;

class UserIsAdminRule extends BaseValidationRule
{
    /**
     * @param  IsAdminPayload  $payload
     *
     * @throws RuleValidation
     */
    protected function validation(BaseValidationPayload $payload): void
    {
        if ($payload->isAdmin === false) {
            throw new RuleValidation($this, 'No permission', $payload, 'admin_required');
        }
    }

    protected function key(): string
    {
        return 'user_is_admin';
    }

    public function payloadObjectClass(): string
    {
        return IsAdminPayload::class;
    }

    protected function inverseValidationException(BaseValidationPayload $payload): RuleValidation
    {
        throw new RuleValidation($this, 'data matches but should not', $payload);
    }
}
