<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Core;

use Jkbennemann\Foliage\Core\Contracts\RuleParserContract;
use Jkbennemann\Foliage\Exceptions\RuleValidation;
use ReflectionClass;
use ReflectionException;

class RuleParser implements RuleParserContract
{
    public function __construct(private readonly array $availableRules)
    {
    }

    /**
     * @throws ReflectionException
     * @throws RuleValidation
     */
    public function parse(?string $ruleName): ?BaseValidationRule
    {
        if (! $ruleName) {
            return null;
        }

        foreach ($this->availableRules as $possibleRule) {
            $rule = new ReflectionClass($possibleRule);
            if (! $rule->isSubclassOf(BaseValidationRule::class)) {
                continue;
            }

            $rule = $this->getRuleInstance($rule);

            /** @var BaseValidationRule $rule */
            if ($rule->normalizedKey() === $ruleName || $rule::class === $ruleName) {
                return $rule;
            }

            $rule = null;
        }

        /** @var BaseValidationRule|string $rule */
        $rule = $this->getRule($ruleName);

        if ($rule instanceof BaseValidationRule) {
            throw RuleValidation::notEnabled($rule);
        }

        throw RuleValidation::invalidRule($rule);
    }

    private function getRule(string $ruleName): BaseValidationRule|string
    {
        try {
            $rule = new ReflectionClass($ruleName);

            $rule = $this->getRuleInstance($rule);

            if (! $rule instanceof BaseValidationRule) {
                return $ruleName;
            }

            return $rule;
        } catch (ReflectionException) {
            return $ruleName;
        }
    }

    /**
     * @throws ReflectionException
     */
    private function getRuleInstance(ReflectionClass $rule): mixed
    {
        if (function_exists('resolve')) {
            return resolve($rule->getName());
        }

        return $rule->newInstance();
    }
}
