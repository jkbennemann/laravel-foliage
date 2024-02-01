<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core;

use Jkbennemann\BusinessRequirements\Core\Payload\ArrayPayload;
use Jkbennemann\BusinessRequirements\Core\Payload\BaseValidationPayload;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;
use Throwable;

abstract class BaseValidationRule
{
    public function __construct(protected array $data = [])
    {
    }

    public function setSettings(array $data): void
    {
        $this->data = $data;
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

    /**
     * @throws RuleValidation
     */
    public function validate(BaseValidationPayload $payload, bool $negate = false): void
    {
        try {
            $this->validateRule($payload, $negate);
        } catch (RuleValidation $specificRuleException) {
            throw $specificRuleException;
        } catch (Throwable) {
            throw RuleValidation::unexpected();
        }
    }

    /**
     * @throws RuleValidation
     */
    private function validateRule(BaseValidationPayload $payload, bool $negate = false): void
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
     * @param  BaseValidationPayload  $payload  data to validate against
     *
     * @throws RuleValidation exception
     */
    abstract protected function validation(BaseValidationPayload $payload): void;

    /**
     * Database key name of validation rule e.g. availability, user_groups, users.
     */
    abstract protected function key(): string;

    /**
     * Exception that will be thrown in case the rule is supposed to be a NOT rule.
     * This will be the case whenever a certain rule should be reversed.
     *
     * @param  BaseValidationPayload  $payload  data to validate against
     */
    abstract protected function inverseValidationException(BaseValidationPayload $payload): RuleValidation;
}
