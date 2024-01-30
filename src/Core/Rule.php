<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core;

use Illuminate\Contracts\Container\BindingResolutionException;
use Jkbennemann\BusinessRequirements\Core\Payload\BaseValidationPayload;
use Jkbennemann\BusinessRequirements\Validator\TreeValidator;
use ReflectionException;

class Rule
{
    public function __construct(
        private ?Node $node
    ) {
        $this->node = $node ?: resolve(Node::class);
    }

    public static function fromNode(Node $node): Rule
    {
        return new Rule($node);
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    public static function single(string $rule, array|BaseValidationPayload $data, ?Node $parent = null): Rule
    {
        /**
         * @var BaseValidationRule $ruleInstance
         */
        $ruleInstance = resolve($rule, ['data' => []]);
        $builder = new TreeBuilder();
        $node = new Node(new TreeValidator());
        $nodeData = [
            'type' => Node::TYPE_LEAF,
            'rule' => $ruleInstance->normalizedKey(),
            'data' => $data instanceof BaseValidationPayload ? $data->toArray() : $data,
        ];
        $node = $builder->buildNode($node, $ruleInstance->normalizedKey(), $nodeData, $parent);

        return new Rule($node);
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    public static function and(self|array ...$rules): Rule
    {
        return self::createNode(Node::OPERATION_AND, $rules);
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    public static function or(self|array ...$rules): Rule
    {
        return self::createNode(Node::OPERATION_OR, $rules);
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    public static function not(string $rule, array|BaseValidationPayload $data): Rule
    {
        $rule = self::single($rule, $data);
        $rule->node->operation = Node::OPERATION_NOT;

        return $rule;
    }

    public static function empty(): Rule
    {
        return new Rule(null);
    }

    public function node(): Node
    {
        return $this->node;
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    private static function createNode(string $operation, self|array $rules): Rule
    {
        $rootNode = resolve(Node::class);
        $rootNode->operation = $operation;
        $rootNode->isLeaf = false;

        if (is_array($rules)) {
            foreach ($rules as $rule) {
                $child = $rule;
                if (is_array($rule)) {
                    $node = self::single($rule[0], $rule[1], $rootNode);
                    $child = $node;
                }

                $rootNode->children[] = $child->node();
            }
        }

        return new Rule($rootNode);
    }

    public function toArray(): array
    {
        if (! $this->node) {
            return [];
        }

        return $this->node->toArray();
    }

    public function jsonSerialize(): string
    {
        return json_encode($this->toArray());
    }
}
