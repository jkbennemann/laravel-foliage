<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Validator;

use Exception;
use Jkbennemann\Foliage\Core\BaseValidationRule;
use Jkbennemann\Foliage\Core\Payload\ArrayPayload;
use Jkbennemann\Foliage\Core\Payload\BaseValidationPayload;
use Jkbennemann\Foliage\Validator\Contracts\ValidationDataContract;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Throwable;

class ValidationDataBuilder implements ValidationDataContract
{
    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function build(BaseValidationRule|string $rule, array $payload, ?string $alias = null): BaseValidationPayload
    {
        /** @var BaseValidationRule $rule */
        $rule = $this->getRuleInstance($rule);

        return $this->composePayloadInstance($rule, $payload, $alias);
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

        return resolve($rule);

    }

    private function composePayloadInstance(
        BaseValidationRule $rule,
        array $payload,
        ?string $alias
    ): BaseValidationPayload {
        //all payloads use Spatie's Data package
        try {
            if ($rule->payloadObjectClass() === ArrayPayload::class) {
                return ArrayPayload::from(['data' => $payload]);
            } else {
                $payloadClass = $rule->payloadObjectClass();

                if ($alias && array_key_exists($alias, $payload)) {
                    $payloadClassParameters = $this->getPayloadParameters($payloadClass);

                    if (count($payloadClassParameters) === 1) {
                        $payload[$payloadClassParameters[0]] = $payload[$alias] ?? [];
                    }
                }

                return $rule->payloadObjectClass()::from($payload);
            }
        } catch (Throwable) {
            return ArrayPayload::from(['data' => $payload]);
        }
    }

    /**
     * @throws ReflectionException
     */
    private function getPayloadParameters(string $payloadClass): array
    {
        $reflection = new ReflectionClass($payloadClass);
        $parameters = $reflection->getConstructor()->getParameters();

        return collect($parameters)
            ->map(function (ReflectionParameter $param) {
                return $param->getName();
            })
            ->flatten()
            ->toArray();
    }
}
