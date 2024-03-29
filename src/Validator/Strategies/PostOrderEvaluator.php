<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Validator\Strategies;

use Jkbennemann\Foliage\Core\Node;
use Jkbennemann\Foliage\Exceptions\RuleValidation;
use Jkbennemann\Foliage\Validator\Contracts\ValidationStrategy;

class PostOrderEvaluator extends ValidationStrategy
{
    /**
     * Validation logic of the rule.
     *
     * @throws RuleValidation exception
     */
    private function evaluateLeaf(Node $leafNode, array $payload, ?Node $parent): void
    {
        $rule = $leafNode->rule;
        $validationPayload = $this->payloadBuilder->build($rule, $payload, $leafNode->alias);

        $validationPayload->setUpdate($payload['is_update'] ?? false);

        $rule->validate(
            $validationPayload,
            $leafNode->operation === Node::OPERATION_NOT
        );
    }

    /**
     * @throws RuleValidation
     */
    public function evaluateNode(?Node $node, array $payload, ?Node $parent): ?bool
    {
        if ($node->isLeaf) {
            try {
                $this->evaluateLeaf($node, $payload, $parent);

                return true;
            } catch (RuleValidation $exception) {
                if ($parent === null) {
                    $this->validationErrors = collect([$exception]);

                    $this->isValid = false;

                    if ($this->raiseException) {
                        throw $exception;
                    }

                    return false;
                }

                throw $exception;
            }
        }

        if ($node !== null) {
            $result = match ($node->operation) {
                Node::OPERATION_AND => $this->evaluateAnd($node, $payload),
                Node::OPERATION_OR => $this->evaluateOr($node, $payload),
                default => true,
            };

            $this->isValid = $result;

            return $result;
        }

        return null;
    }

    private function evaluateAnd(Node $node, array $payload): bool
    {
        $errors = collect();
        $isValidLeft = false;
        $isValidRight = false;

        //        $this->validationErrors = collect();

        try {
            $isValidLeft = $this->evaluateNode($node->children->get(0), $payload, $node);

            $n = '';
        } catch (RuleValidation $exception) {
            $errors->push($exception);
        }

        try {
            $isValidRight = $this->evaluateNode($node->children->get(1), $payload, $node);

            if ($isValidRight === false) {
                foreach ($this->errors() as $error) {
                    $errors->add($error);
                }
            }
        } catch (RuleValidation $exception) {
            $errors->push($exception);
        }

        $this->validationErrors = $errors;

        if ($this->raiseException && $errors->count() > 0) {
            throw $errors->first();
        }

        return $isValidLeft && $isValidRight;
    }

    private function evaluateOr(Node $node, array $payload): bool
    {
        $errors = collect();

        try {
            $result = $this->evaluateNode($node->children->get(0), $payload, $node);

            if ($result === true) {
                $this->validationErrors = collect();

                return true;
            }

            if ($result === false) {
                foreach ($this->errors() as $error) {
                    $errors->add($error);
                }
            }
        } catch (RuleValidation $exception) {
            $errors->push($exception);
        }

        try {
            $result = $this->evaluateNode($node->children->get(1), $payload, $node);

            if ($result === true) {
                $this->validationErrors = collect();

                return true;
            }

            if ($result === false) {
                foreach ($this->errors() as $error) {
                    $errors->add($error);
                }
            }
        } catch (RuleValidation $exception) {
            $errors->push($exception);
        }

        $this->validationErrors = $errors;

        if ($this->raiseException && $errors->count() > 0) {
            throw $errors->first();
        }

        return false;
    }
}
