<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use JsonSerializable;

class Node implements Arrayable, JsonSerializable
{
    public const OPERATION_AND = 'AND';

    public const OPERATION_OR = 'OR';

    public const OPERATION_NOT = 'NOT';

    public const TYPE_LEAF = 'leaf';

    public const TYPE_NODE = 'node';

    public bool $isLeaf = false;

    public ?BaseValidationRule $rule = null;

    public ?Node $parent = null;

    public Collection $children;

    public ?string $operation = null;

    public ?string $alias = null;

    public function __construct()
    {
        $this->children = new Collection();
    }

    public function addChild(self $child): self
    {
        $this->children->push($child);

        return $this;
    }

    public function setAlias(?string $alias = null): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->isLeaf === false
            && $this->rule === null
            && $this->parent === null
            && $this->operation === null;
    }

    public function toArray(): array
    {
        $data = [
            'alias' => $this->alias,
            'type' => $this->isLeaf ? self::TYPE_LEAF : self::TYPE_NODE,
            'operation' => $this->operation,
            'name' => $this->rule?->normalizedKey(),
            'data' => $this->rule?->settings(),
            'children' => $this->children->toArray(),
        ];

        ksort($data);

        return $data;
    }

    public function rulesFlattened(): Collection
    {
        return $this->flatten($this);
    }

    private function flatten(self $node): Collection
    {
        $rules = collect();

        if ($node->rule instanceof BaseValidationRule) {
            $rules->add($node->rule);
        }

        foreach ($node->children as $child) {
            if ($child->rule == null && count($child->children) == 0) {
                continue;
            }

            $rules = $rules->merge($this->flatten($child));
        }

        return $rules;
    }

    public function ruleNodes(self $node): Collection
    {
        $rules = collect();

        if ($node->isLeaf) {
            $rules->add($node);

            return $rules;
        }

        /** @var Node $child */
        foreach ($node->children as $child) {
            if ($child->isLeaf) {
                $rules->add($child);

                continue;
            }

            $rules = $rules->merge($this->ruleNodes($child));
        }

        return $rules;
    }

    public function getRule(string $rule, Node $parent): ?BaseValidationRule
    {
        if ($parent->isLeaf && $parent->rule instanceof $rule) {
            return $parent->rule;
        }

        if (! $parent->isLeaf) {
            foreach ($parent->children as $child) {
                return $this->getRule($rule, $child);
            }
        }

        return null;
    }

    public function addNode(?string $operation): self
    {
        $tmpNode = app(Node::class);
        $tmpNode->operation = $operation;

        if ($this->children->isEmpty() || $this->children->count() === 1) {
            $this->children->push($tmpNode);

            return $this;
        }

        $child = $this->findIncompleteNode($this->children);
        $child->children->push($tmpNode);

        return $this;
    }

    public function jsonSerialize(): string
    {
        return json_encode($this->toArray());
    }

    public function isBinary(): bool
    {
        if ($this->isLeaf) {
            return true;
        }

        if ($this->children->count() > 2) {
            return false;
        }

        /** @var Node $child */
        foreach ($this->children as $child) {
            if (!$child->isBinary()) {
                return false;
            }
        }

        return true;
    }

    private function findIncompleteNode(Collection $children): ?Node
    {
        /** @var Node $childNode */
        foreach ($children as $key => $childNode) {
            if ($childNode->isLeaf) {
                continue;
            }

            if ($childNode->children->isEmpty() || $childNode->children->count() === 1) {
                return $childNode;
            }

            //children count is 2,
            $childCountNextNode = $children->get($key + 1)?->children->count();

            if ($childCountNextNode !== null && $childCountNextNode < 2) {
                continue;
            }

            $availableNode = $this->findIncompleteNode($childNode->children);

            if ($availableNode) {
                return $availableNode;
            }
        }

        return null;
    }
}
