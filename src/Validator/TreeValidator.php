<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Validator;

use Exception;
use Illuminate\Support\Collection;
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

        $rootNode->isLeaf
            ? $this->evaluateLeaf($rootNode, $payload)
            : $this->evaluateNode($rootNode, $payload);
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
        $result = $node->operation === Node::OPERATION_AND;

        $lastDisjunctionError = null;
        $isValid = false;
        /** @var Node $childNode */
        foreach ($node->children as $childNode) {
            if ($node->operation === Node::OPERATION_AND) {
                try {
                    $this->evaluate($childNode, $payload);

                    $result = true;
                    $isValid = true;

                    continue;
                } catch (RuleValidation $exception) {
                    $this->validationErrors->add($exception);
                }
            }

            if ($node->operation === Node::OPERATION_OR) {
                try {
                    $this->evaluate($childNode, $payload);

                    $result = true;
                    $isValid = true;
                } catch (RuleValidation $exception) {
                    if (! $isValid) {
                        //                        throw $exception;
                    }
                    $this->validationErrors->add($exception);
                    $lastDisjunctionError = $exception;
                }
            }
        }

        //push error for OR validation if needed.
        if ($result === false && $lastDisjunctionError && ! $node->isLeaf) {
            throw $lastDisjunctionError;
        }
    }

    public function errors(): Collection
    {
        return $this->validationErrors;
    }
}
