<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Jkbennemann\BusinessRequirements\Core\Contracts\ValidationPayloadContract;
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
        $root = resolve(Node::class);

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
        array|ValidationPayloadContract $ruleData,
        ?Node $parent
    ): Node {
        /*
         * Build the node based on the type in $ruleData['type']
         * - Create a rule instance if it's a "leaf"
         * - Create a child node if it's a "node" -> can be seen as decider class aka AND/OR node
         */
        if ($ruleData instanceof ValidationPayloadContract) {
            $ruleData = $ruleData->getData();
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
}
