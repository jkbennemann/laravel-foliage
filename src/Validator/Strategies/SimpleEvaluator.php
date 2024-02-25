<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Validator\Strategies;

use Exception;
use Jkbennemann\Foliage\Core\Node;
use Jkbennemann\Foliage\Exceptions\RuleValidation;
use Jkbennemann\Foliage\Validator\Contracts\ValidationStrategy;

class SimpleEvaluator extends ValidationStrategy
{
    /**
     * Validation logic of the rule.
     *
     * @throws RuleValidation exception
     */
    private function evaluateLeaf(Node $leafNode, array $payload): bool
    {
        $rule = $leafNode->rule;
        $validationPayload = $this->payloadBuilder->build($rule, $payload, $leafNode->alias);

        $validationPayload->setUpdate($payload['is_update'] ?? false);

        try {
            $rule->validate(
                $validationPayload,
                $leafNode->operation === Node::OPERATION_NOT
            );

            return true;
        } catch (RuleValidation $exception) {
            $this->validationErrors->add($exception);

            throw $exception;
        }
    }

    /**
     * @throws RuleValidation|Exception
     */
    public function evaluateNode(Node $node, array $payload, ?Node $parent): bool
    {
        if ($node->isLeaf) {
            try {
                $isValid = $this->evaluateLeaf($node, $payload);

                if ($isValid) {
                    $this->isValid = true;
                }

                return true;
            } catch (RuleValidation $exception) {
                $this->isValid = false;

                if ($this->raiseException) {
                    throw $exception;
                }

                return false;
            }
        }

        $disjunctionRulesFailed = 0;
        $disjunctionRules = 0;

        $childrenResult = true;
        /** @var Node $childNode */
        foreach ($node->children as $childNode) {
            if ($node->operation === Node::OPERATION_AND) {
                try {
                    $isValid = $this->evaluateNode($childNode, $payload, null);

                    if (! $isValid) {
                        $childrenResult = false;
                    }

                    continue;
                } catch (RuleValidation) {
                    $childrenResult = false;
                }
            }

            if ($node->operation === Node::OPERATION_OR) {
                $disjunctionRules++;

                try {
                    $childrenResult = $this->evaluateNode($childNode, $payload, null);

                    if (! $childrenResult) {
                        $disjunctionRulesFailed++;
                    }
                } catch (RuleValidation) {
                    $disjunctionRulesFailed++;
                }
            }
        }

        if ($disjunctionRules === $disjunctionRulesFailed && $disjunctionRules !== 0) {
            $childrenResult = false;
        }

        if ($childrenResult === true || ($disjunctionRulesFailed !== $disjunctionRules && $disjunctionRules !== 0)) {
            $this->isValid = true;

            return true;
        }

        $this->isValid = false;

        if ($this->raiseException) {
            throw $this->errors()->first();
        }

        return false;
    }
}
