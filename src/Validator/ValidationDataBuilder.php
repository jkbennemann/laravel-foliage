<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Validator;

use _PHPStan_11268e5ee\Nette\Neon\Exception;
use Jkbennemann\BusinessRequirements\Core\BaseValidationRule;
use Jkbennemann\BusinessRequirements\Core\Payload\ArrayPayload;
use Jkbennemann\BusinessRequirements\Core\Payload\BaseValidationPayload;
use Jkbennemann\BusinessRequirements\Validator\Contracts\ValidationDataContract;
use ReflectionClass;
use ReflectionException;

class ValidationDataBuilder implements ValidationDataContract
{
    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function build(BaseValidationRule|string $rule, array $payload): BaseValidationPayload
    {
        /** @var BaseValidationRule $rule */
        $rule = $this->getRuleInstance($rule);

        return $this->composePayloadInstance($rule, $payload);
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    private function getRuleInstance(BaseValidationRule|string $rule): BaseValidationRule
    {
        if ($rule instanceof BaseValidationRule) {
            return $rule;
        }

        $reflection = new ReflectionClass($rule);
        $isPayloadSubclass = $reflection->isSubclassOf(BaseValidationRule::class);

        if (! $isPayloadSubclass) {
            throw new Exception('Invalid argument for `$rule` specified. Your class needs to extend the BaseValidationRule class');
        }

        return $reflection->newInstance([]);

    }

    private function composePayloadInstance(
        BaseValidationRule $rule,
        array $payload
    ): BaseValidationPayload {
        //all payloads use Spatie's Data package
        if ($rule->payloadObjectClass() === ArrayPayload::class) {
            return ArrayPayload::from(['data' => $payload]);
        } else {
            return $rule->payloadObjectClass()::from($payload);
        }
    }
}
