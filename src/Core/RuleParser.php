<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core;

use Jkbennemann\BusinessRequirements\Core\Contracts\RuleParserContract;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;
use ReflectionClass;
use ReflectionException;

class RuleParser implements RuleParserContract
{
    public function __construct(private readonly array $availableRules)
    {
    }

    public function getAvailableRules(): array
    {
        return $this->availableRules;
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

        $rule = null;

        foreach ($this->availableRules as $possibleRule) {
            $rule = new ReflectionClass($possibleRule);
            if (! $rule->isSubclassOf(BaseValidationRule::class)) {
                continue;
            }

            if (function_exists('resolve')) {
                $rule = resolve($rule->getName());
            } else {
                $rule = $rule->newInstance();
            }

            /** @var BaseValidationRule $rule */
            if ($rule->normalizedKey() === $ruleName || $rule::class === $ruleName) {
                return $rule;
            }
        }

        throw RuleValidation::notEnabled($rule);
    }
}
