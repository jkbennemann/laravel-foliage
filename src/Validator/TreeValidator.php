<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Validator;

use Exception;
use Illuminate\Support\Collection;
use Jkbennemann\BusinessRequirements\Core\Node;
use Jkbennemann\BusinessRequirements\Core\Payload\ArrayPayload;
use Jkbennemann\BusinessRequirements\Core\Payload\DateAvailabilityPayload;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;
use Jkbennemann\BusinessRequirements\Validator\Contracts\Validator;
use ReflectionClass;
use ReflectionException;

class TreeValidator implements Validator
{
    protected Collection $validationErrors;

    public function __construct()
    {
        $this->validationErrors = new Collection();
    }

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
     * @throws RuleValidation|ReflectionException exception
     */
    private function evaluateLeaf(Node $leafNode, array $payload): void
    {
        $reflectionClass = new ReflectionClass($leafNode->rule->payloadObjectClass());
        $parameters = $reflectionClass->getConstructor()->getParameters();
        $useData = [];

        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();
            $normalizedRuleKey = $leafNode->rule->normalizedKey();
            if (array_key_exists($normalizedRuleKey, $payload)) {
                $data = $payload[$normalizedRuleKey];
                $useData[$parameterName] = is_array($data) ? $data : [$data];
            } elseif (array_key_exists($parameterName, $payload)) {
                $data = $payload[$parameterName];
                $useData[$parameterName] = $data;
                if ($leafNode->rule->payloadObjectClass() === ArrayPayload::class) {
                    $useData[$parameterName] = is_array($data) ? $data : [$data];
                }
            } elseif ($leafNode->rule->payloadObjectClass() === ArrayPayload::class) {
                $useData['data'] = collect($payload)->flatten()->toArray();
            }
        }

        if ($leafNode->rule->payloadObjectClass() === DateAvailabilityPayload::class) {
            $date = is_array($useData['date']) ? $useData['date'][0] : $useData['date'];
            $validationData = app($leafNode->rule->payloadObjectClass(), ['date' => $date]);
        } else {
            $validationData = app($leafNode->rule->payloadObjectClass(), $useData);
        }

        if (isset($payload['is_update'])) {
            $validationData->setUpdate($payload['is_update']);
        }

        try {
            $leafNode->rule->validate(
                $validationData,
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
