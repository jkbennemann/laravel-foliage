<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Jkbennemann\BusinessRequirements\Core\Payload\BaseValidationPayload;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;
use Jkbennemann\BusinessRequirements\Exceptions\TreeBuilderException;
use ReflectionClass;
use ReflectionException;

class TreeBuilder
{
    private array $availableRules;

    public function __construct()
    {
        $this->availableRules = config('validate-business-requirements.available_rules', []);
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function build(array $rules): Node
    {
        $this->validate($rules);

        $root = new Node();

        if ($this->isSingleRule($rules)) {
            $rules = [$rules];
        }

        foreach ($rules as $ruleData) {
            $this->buildNode(
                $root,
                $ruleData['name'],
                $ruleData,
                null
            );
        }

        return $root;
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function buildNode(
        Node $node,
        ?string $ruleKey,
        array|BaseValidationPayload $ruleData,
        ?Node $parent
    ): Node {
        /*
         * Build the node based on the type in $ruleData['type']
         * - Create a rule instance if it's a "leaf"
         * - Create a child node if it's a "node" -> can be seen as decider class aka AND/OR node
         */
        if ($ruleData instanceof BaseValidationPayload) {
            $ruleData = $ruleData->toArray();
        }

        if ($ruleData['type'] === Node::TYPE_LEAF) {
            //create rule
            $rule = null;
            //find correct rule
            foreach ($this->availableRules as $possibleRule) {
                $rule = new ReflectionClass($possibleRule);
                if (! $rule->isSubclassOf(BaseValidationRule::class)) {
                    continue;
                }

                /**
                 * @var BaseValidationRule $ruleInstance
                 */
                $ruleInstance = resolve($rule->getName(), ['data' => $ruleData['data']]);
                if ($ruleInstance->normalizedKey() === $ruleKey) {
                    $node->isLeaf = true;
                    $node->rule = $ruleInstance;
                    $node->parent = $parent;
                    $node->operation = $ruleData['operation'] ?? null;
                    break;
                }

                $rule = null;
            }

            if (! $rule) {
                throw RuleValidation::notEnabled($rule);
            }

            return $node;
        }

        //process child nodes
        foreach ($ruleData['children'] as $childRuleData) {
            $node->isLeaf = false;
            $node->operation = $ruleData['operation'];

            $childNode = resolve(Node::class);
            $node->addChild(
                $this->buildNode(
                    $childNode,
                    $childRuleData['name'],
                    $childRuleData,
                    $node
                )
            );
        }

        return $node;
    }

    /**
     * @throws ReflectionException
     * @throws TreeBuilderException
     */
    public function convertFromRequest(?array $requestRules): ?array
    {
        if ($requestRules === null || count($requestRules) === 0) {
            return null;
        }

        $type = $requestRules['operation'] === null
            ? 'single'
            : $requestRules['operation'];
        $ruleName = $requestRules['name'];
        $data = $requestRules['data'];
        $children = $requestRules['children'];

        return $this->buildRule($type, $ruleName, $children, $data)->toArray();
    }

    /**
     * @throws ReflectionException
     * @throws TreeBuilderException
     */
    private function buildRule(
        ?string $operation,
        ?string $ruleName,
        array $children,
        ?array $data
    ): Rule {
        //if operation is null: it has to be a single Rule
        if (count($children) === 0) {
            return $this->buildLeafRule($ruleName, $data, $operation);
        }

        $tmpRules = [];
        foreach ($children as $child) {
            $tmpRules[] = $this->buildRule($child['operation'], $child['name'], $child['children'], $child['data']);
        }

        $operation = strtolower($operation);

        return Rule::$operation(
            ...$tmpRules
        );
    }

    /**
     * @throws ReflectionException
     * @throws TreeBuilderException
     */
    private function buildLeafRule(string $ruleName, array $data, ?string $operation): Rule
    {
        foreach ($this->availableRules as $rule) {
            /**
             * @var BaseValidationRule $ruleInstance
             */
            $reflection = new ReflectionClass($rule);
            $ruleInstance = $reflection->newInstance([]);
            if ($ruleInstance->normalizedKey() === $ruleName) {
                $ruleMethod = 'single';
                if ($operation === Node::OPERATION_NOT) {
                    $ruleMethod = 'not';
                }

                return Rule::$ruleMethod(get_class($ruleInstance), $data);
            }
        }

        throw new TreeBuilderException(
            sprintf(
                'Cannot build leaf node for [%s]',
                $ruleName
            ),
            500
        );
    }

    private function isSingleRule(array $data): bool
    {
        return array_key_exists('name', $data);
    }

    /**
     * @throws Exception
     */
    private function validate(array $rules): void
    {
        if (isset($rules['children']) && is_array($rules['children'])) {
            foreach ($rules['children'] as $child) {
                $this->validate($child);
            }
        } else {
            $this->checkType($rules);
            if ($rules['type'] === 'leaf') {
                $this->checkLeaf($rules);
            } elseif ($rules['type'] === 'node') {
                $this->checkNode($rules);
            }
        }
    }

    /**
     * @throws Exception
     */
    private function checkType(array $rules): void
    {
        $validTypes = ['leaf', 'node'];
        if (!isset($rules['type']) || !in_array($rules['type'], $validTypes)) {
            throw new Exception("Invalid or missing 'type' value. Must be either 'leaf' or 'node'.");
        }
    }

    /**
     * @throws Exception
     */
    private function checkLeaf(array $rules): void
    {
        if (!isset($rules['data']) || !is_array($rules['data'])) {
            throw new Exception("'data' must be an array for 'leaf' type.");
        }

        if (isset($rules['operation'])) {
            throw new Exception("'operation' must be null for 'leaf' type.");
        }

        if (!empty($rules['children'])) {
            throw new Exception("'children' must be an empty array for 'leaf' type.");
        }

        if (!isset($rules['name']) || !is_string($rules['name'])) {
            throw new Exception("'name' must be a string for 'leaf' type.");
        }
    }

    /**
     * @throws Exception
     */
    private function checkNode(array $rules): void
    {
        if (isset($rules['data'])) {
            throw new Exception("'data' must be null for 'node' type.");
        }

        if (!isset($rules['children']) || !is_array($rules['children'])) {
            throw new Exception("'children' must be an array for 'node' type.");
        }

        if (isset($rules['name'])) {
            throw new Exception("'name' must be null for 'node' type.");
        }

        if (!isset($rules['operation'])) {
            throw new Exception("'operation' must not be null for 'node' type.");
        }

        foreach ($rules['children'] as $child) {
            $this->validate($child);
        }
    }
}
