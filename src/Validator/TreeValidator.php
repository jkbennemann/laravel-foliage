<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Validator;

use Exception;
use Jkbennemann\BusinessRequirements\Core\Node;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;
use Jkbennemann\BusinessRequirements\Validator\Contracts\BaseValidator;

class TreeValidator extends BaseValidator
{
    /**
     * @throws RuleValidation
     */
    public function evaluate(Node $rootNode, array $payload): bool
    {
        if ($rootNode->isEmpty()) {
            return true;
        }

        return $this->evaluateNode($rootNode, $payload);
    }

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
    private function evaluateNode(Node $node, array $payload): bool
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
            //TODO: extract the following code into a strategy
            //$strategy = $operationFactory->make($node->operation);
            //$result = $strategy->execute($childNode, $payload);

            if ($node->operation === Node::OPERATION_AND) {
                try {
                    $isValid = $this->evaluate($childNode, $payload);

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
                    $childrenResult = $this->evaluate($childNode, $payload);

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
