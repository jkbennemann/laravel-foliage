<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Jkbennemann\BusinessRequirements\Core\Payload\BaseValidationPayload;
use Jkbennemann\BusinessRequirements\Validator\Contracts\BaseValidator;
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

    public function __construct(private readonly BaseValidator $validator)
    {
        $this->children = new Collection();
    }

    public function addChild(self $child): self
    {
        $this->children->push($child);

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
            'type' => $this->isLeaf ? self::TYPE_LEAF : self::TYPE_NODE,
            'operation' => $this->operation,
            'name' => $this->rule?->normalizedKey(),
            'data' => $this->rule?->settings(),
            'children' => $this->children->toArray(),
        ];

        ksort($data);

        return $data;
    }

    public function validate(BaseValidationPayload|array $payload): void
    {
        if ($payload instanceof BaseValidationPayload) {
            $payload = $payload->toArray();
        }

        $this->validator->evaluate($this, $payload);
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

    private function getRule(string $rule, Node $parent): ?BaseValidationRule
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

    public function jsonSerialize(): string
    {
        return json_encode($this->toArray());
    }
}
