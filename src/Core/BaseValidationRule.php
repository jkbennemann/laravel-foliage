<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core;

use Exception;
use Jkbennemann\BusinessRequirements\Core\Contracts\ValidationPayloadContract;
use Jkbennemann\BusinessRequirements\Core\Payload\ArrayPayload;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;

abstract class BaseValidationRule
{
    public function __construct(protected array $data)
    {
    }

    final public function normalizedKey(): string
    {
        return strtolower($this->key());
    }

    public function settings(): array
    {
        return $this->data;
    }

    public function payloadObjectClass(): string
    {
        return ArrayPayload::class;
    }

    public function validate(ValidationPayloadContract $payload, bool $negate = false): void
    {
        try {
            $this->validateRule($payload, $negate);
        } catch (RuleValidation $specificRuleException) {
            throw $specificRuleException;
        } catch (Exception) {
            throw RuleValidation::unexpected();
        }
    }

    /**
     * @throws RuleValidation
     */
    private function validateRule(ValidationPayloadContract $payload, bool $negate = false): void
    {
        if ($negate) {
            try {
                $this->validation($payload);
            } catch (RuleValidation) {
                //it's fine as rule is inverse and is supposed to fail
                return;
            }

            throw $this->inverseValidationException($payload);
        }

        $this->validation($payload);
    }

    /**
     * Validation logic of the rule.
     *
     * @param  ValidationPayloadContract  $payload  data to validate against
     *
     * @throws RuleValidation exception
     */
    abstract protected function validation(ValidationPayloadContract $payload): void;

    /**
     * Database key name of validation rule e.g. availability, user_groups, users.
     */
    abstract protected function key(): string;

    /**
     * Exception that will be thrown in case the rule is supposed to be a NOT rule.
     * This will be the case whenever a certain rule should be reversed.
     *
     * @param  ValidationPayloadContract  $payload  data to validate against
     */
    abstract protected function inverseValidationException(ValidationPayloadContract $payload): RuleValidation;
}
