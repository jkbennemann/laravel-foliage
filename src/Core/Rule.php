<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core;

use Illuminate\Contracts\Container\BindingResolutionException;
use Jkbennemann\BusinessRequirements\Core\Contracts\RuleParserContract;
use Jkbennemann\BusinessRequirements\Core\Payload\BaseValidationPayload;
use Jkbennemann\BusinessRequirements\Validator\Normalizer;
use ReflectionException;

class Rule
{
    public function __construct(
        private readonly RuleParserContract $ruleParser,
        private ?Node $node = null,
    ) {
        $this->node = $node ?: new Node();
    }

    public function fromNode(Node $node): Rule
    {
        return new Rule($this->ruleParser, $node);
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    public function single(Rule|string $rule, array|BaseValidationPayload $data = [], ?Node $parent = null): Rule
    {
        if (is_string($rule)) {
            $rule = $this->ruleParser->parse($rule);
        }
        /**
         * @var BaseValidationRule $rule
         */
        $builder = new TreeBuilder($this->ruleParser);

        $nodeData = [
            'type' => Node::TYPE_LEAF,
            'rule' => $rule->normalizedKey(),
            'data' => $data instanceof BaseValidationPayload ? $data->toArray() : $data,
        ];
        $node = $builder->buildNode(new Node(), $rule, $nodeData, $parent);

        return new Rule($this->ruleParser, $node);
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    public function and(self|array ...$rules): Rule
    {
        return $this->createNode(Node::OPERATION_AND, $rules);
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    public function or(self|array ...$rules): Rule
    {
        return $this->createNode(Node::OPERATION_OR, $rules);
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    public function not(string $rule, array|BaseValidationPayload $data = []): Rule
    {
        $rule = $this->single($rule, $data);
        $rule->node->operation = Node::OPERATION_NOT;

        return $rule;
    }

    public function alias(?string $alias = null): Rule
    {
        if ($this->node()->isLeaf) {
            $this->node()->setAlias($alias);
        }

        return $this;
    }

    public function empty(): Rule
    {
        return new Rule($this->ruleParser);
    }

    public function node(): Node
    {
        if (! $this->node->isBinary()) {
            $normalizer = new Normalizer();
            $this->node = $normalizer->normalize(self::fromNode($this->node))->node;
        }

        return $this->node;
    }

    public function rawNode(): Node
    {
        return $this->node;
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    private function createNode(string $operation, self|array $rules): Rule
    {
        $rootNode = new Node();
        $rootNode->operation = $operation;
        $rootNode->isLeaf = false;

        if (is_array($rules)) {
            foreach ($rules as $rule) {
                $child = $rule;
                if (is_array($rule)) {
                    $node = $this->single($rule[0], $rule[1], $rootNode);
                    $child = $node;
                }

                $rootNode->children[] = $child->node();
            }
        }

        return new Rule($this->ruleParser, $rootNode);
    }

    public function toArray(): array
    {
        if (! $this->node) {
            return [];
        }

        return $this->node()->toArray();
    }

    public function jsonSerialize(): string
    {
        return json_encode($this->toArray());
    }
}
