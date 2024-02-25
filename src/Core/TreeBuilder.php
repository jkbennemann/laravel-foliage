<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Core;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Jkbennemann\Foliage\Core\Contracts\RuleParserContract;
use Jkbennemann\Foliage\Core\Payload\BaseValidationPayload;
use ReflectionException;

class TreeBuilder
{
    public function __construct(
        private RuleParserContract $ruleParser
    ) {
    }

    public function setRuleParser(RuleParserContract $parser): TreeBuilder
    {
        $this->ruleParser = $parser;

        return $this;
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
                $this->ruleParser->parse($ruleData['name']),
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
        ?BaseValidationRule $rule,
        array|BaseValidationPayload $ruleData,
        ?Node $parent
    ): Node {
        if ($ruleData instanceof BaseValidationPayload) {
            $ruleData = $ruleData->toArray();
        }

        if ($ruleData['type'] === Node::TYPE_LEAF) {
            $rule->setSettings($ruleData['data']);

            //create rule
            $node->isLeaf = true;
            $node->rule = $rule;
            $node->parent = $parent;
            $node->operation = $ruleData['operation'] ?? null;

            return $node;
        }

        //process child nodes
        foreach ($ruleData['children'] as $childRuleData) {
            $node->isLeaf = false;
            $node->operation = $ruleData['operation'];

            $childNode = new Node();
            $node->addChild(
                $this->buildNode(
                    $childNode,
                    $this->ruleParser->parse($childRuleData['name']),
                    $childRuleData,
                    $node
                )
            );
        }

        return $node;
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
        if (! isset($rules['type']) || ! in_array($rules['type'], $validTypes)) {
            throw new Exception("Invalid or missing 'type' value. Must be either 'leaf' or 'node'.");
        }
    }

    /**
     * @throws Exception
     */
    private function checkLeaf(array $rules): void
    {
        if (! isset($rules['data']) || ! is_array($rules['data'])) {
            throw new Exception("'data' must be an array for 'leaf' type.");
        }

        if (isset($rules['operation'])) {
            throw new Exception("'operation' must be null for 'leaf' type.");
        }

        if (! empty($rules['children'])) {
            throw new Exception("'children' must be an empty array for 'leaf' type.");
        }

        if (! isset($rules['name']) || ! is_string($rules['name'])) {
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

        if (! isset($rules['children']) || ! is_array($rules['children'])) {
            throw new Exception("'children' must be an array for 'node' type.");
        }

        if (isset($rules['name'])) {
            throw new Exception("'name' must be null for 'node' type.");
        }

        if (! isset($rules['operation'])) {
            throw new Exception("'operation' must not be null for 'node' type.");
        }

        foreach ($rules['children'] as $child) {
            $this->validate($child);
        }
    }
}
