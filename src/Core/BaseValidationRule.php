<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Core;

use Jkbennemann\Foliage\Core\Payload\ArrayPayload;
use Jkbennemann\Foliage\Core\Payload\BaseValidationPayload;
use Jkbennemann\Foliage\Exceptions\RuleValidation;
use Throwable;

abstract class BaseValidationRule
{
    protected array $data = [];

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
            throw RuleValidation::unexpectedMismatch($this);
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

                $this->validationInverse($payload);
            } catch (RuleValidation) {
                //it's fine as rule is inverse and is supposed to fail
                return;
            }

            throw $this->inverseValidationException($payload);
        }

        $this->validation($payload);
    }

    /**
     * If a rule does not always can be considered a strict logical not
     * you can specify different behaviour for that here.
     *
     * @param  BaseValidationPayload  $payload  data to validate against
     *
     * @throws RuleValidation exception
     */
    protected function validationInverse(BaseValidationPayload $payload): void
    {
        //
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
