<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Validator;

use Exception;
use Jkbennemann\BusinessRequirements\Core\Node;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;
use Jkbennemann\BusinessRequirements\Validator\Contracts\BaseValidator;
use ReflectionException;

class TreeValidator extends BaseValidator
{
    /**
     * @throws ReflectionException
     * @throws RuleValidation
     */
    public function evaluate(Node $rootNode, array $payload): void
    {
        if ($rootNode->isEmpty()) {
            return;
        }

        $this->evaluateNode($rootNode, $payload);
    }

    /**
     * Validation logic of the rule.
     *
     * @throws RuleValidation exception
     */
    private function evaluateLeaf(Node $leafNode, array $payload): void
    {
        $rule = $leafNode->rule;
        $validationPayload = $this->payloadBuilder->build($rule, $payload);

        $validationPayload->setUpdate($payload['is_update'] ?? false);

        try {
            $rule->validate(
                $validationPayload,
                $leafNode->operation === Node::OPERATION_NOT
            );
        } catch (RuleValidation $exception) {
            $this->validationErrors->add($exception);

            throw $exception;
        }
    }

    /**
     * @throws RuleValidation|Exception
     */
    private function evaluateNode(Node $node, array $payload): void
    {
        if ($node->isLeaf) {
            $this->evaluateLeaf($node, $payload);

            return;
        }

        $disjunctionRulesFailed = 0;
        $disjunctionRules = 0;
        /** @var Node $childNode */
        foreach ($node->children as $childNode) {
            if ($node->operation === Node::OPERATION_AND) {
                try {
                    $this->evaluate($childNode, $payload);

                    continue;
                } catch (RuleValidation) {
                }
            }

            if ($node->operation === Node::OPERATION_OR) {
                $disjunctionRules++;
                try {
                    $this->evaluate($childNode, $payload);

                } catch (RuleValidation) {
                    $disjunctionRulesFailed++;
                }
            }
        }

        if ($this->raiseException && $this->errors()->isNotEmpty() && $disjunctionRulesFailed === $disjunctionRules && $disjunctionRules !== 0) {
            throw $this->errors()->first();
        }
    }
}
