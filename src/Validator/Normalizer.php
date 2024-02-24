<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Validator;

use Illuminate\Support\Collection;
use Jkbennemann\BusinessRequirements\Core\Node;
use Jkbennemann\BusinessRequirements\Core\Rule;

class Normalizer
{
    public function normalize(Rule $rule): Rule
    {
        $children = clone $rule->node()->children;
        $baseOperation = $rule->node()->operation;

        $groupedByOperation = $children->groupBy(function (Node $node) {
            return $node->operation;
        });

        if ($groupedByOperation->has(Node::OPERATION_NOT) && !$groupedByOperation->has('')) {
            $groupedByOperation->put('', $groupedByOperation->get(Node::OPERATION_NOT));
            $groupedByOperation->forget(Node::OPERATION_NOT);
        }
        if ($groupedByOperation->has(Node::OPERATION_NOT) && $groupedByOperation->has('')) {
            $nodes = $groupedByOperation->get('');
            $nodes = $nodes->merge($groupedByOperation->get(Node::OPERATION_NOT));
            $groupedByOperation->put('', $nodes);
            $groupedByOperation->forget(Node::OPERATION_NOT);
        }

        $chunkedTrees = $groupedByOperation
            ->map(function (Collection $items, string $operation) use ($baseOperation) {
                $rulesCount = $this->rulesCount($items);
                $neededNodes = $this->getNeededNodes($rulesCount);

                $items = $this->flattenRuleNodes($items, $operation);
                return [
                    'nodes' => $items,
                    'tree_template' => $this->createBaseTree(
                        $neededNodes,
                        $operation,
                        $baseOperation
                    )
                ];
            })
            ->map(function (array $data) {
                return $this->fillTree(
                    $data['tree_template'],
                    $data['nodes'],
                );
            });

        return $this->buildRule($chunkedTrees, $rule->node()->operation);
    }

    private function buildRule(Collection $chunked, ?string $operation): Rule
    {
        $rule = app(Rule::class);

        if ($chunked->count() === 1) {
            //only one type
            /** @var Node $tree */
            $tree = $chunked->first();
            $tree->operation = $operation;

            return $rule->fromNode($tree);
        }

        if ($chunked->count() === 2) {
            //only one type
            $right = $chunked->pop();
            $left = $chunked->pop();

            $node = app(Node::class);
            $node->children->push($left);
            $node->children->push($right);
            $node->operation = $operation;

            return $rule->fromNode($node);
        }

        if ($chunked->count() === 3) {
            //only one type
            $right = $chunked->pop();
            $left = $chunked->pop();
            $first = $chunked->pop();

            //new node for combined right side
            $tmpNode = app(Node::class);
            $tmpNode->children->push($left);
            $tmpNode->children->push($right);
            $tmpNode->operation = $operation;

            //new node for complete tree
            $node = app(Node::class);
            $node->children->push($first);
            $node->children->push($tmpNode);
            $node->operation = $operation;

            return $rule->fromNode($node);
        }

        return \Jkbennemann\BusinessRequirements\Facades\Rule::empty();
    }

    private function rulesCount(Collection $items): int
    {
        $count = 0;
        /** @var Node $node */
        foreach ($items as $node) {
            $count += $node->rulesFlattened()->count();
        }

        return $count;
    }

    private function getNeededNodes(int $rulesCount): int
    {
        return $rulesCount - 1;
    }

    private function createBaseTree(mixed $needed_nodes, string $operation, string $baseOperation): Node
    {
        $node = app(Node::class);

        if ($needed_nodes === 0) {
            return $node;
        }

        $node->operation = $operation ?: $baseOperation;
        for ($i = 1; $i < $needed_nodes; $i++) {
            $node->addNode($operation ?: $baseOperation);
        }

        return $node;
    }

    private function fillTree(Node $tree, Collection $nodes): Node
    {
        /** @var Node $node */
        foreach ($nodes as $node) {
            $leaf = $this->findEmptyLeaf($tree);
            $leaf->children->push($node);
        }

        return $tree;
    }

    private function findEmptyLeaf(Node $tree): ?Node
    {
        if ($tree->isLeaf) {
            return null;
        }

        if ($tree->children->isEmpty()) {
            return $tree;
        }

        $leaf = null;
        /** @var Node $childNode */
        foreach ($tree->children as $childNode) {
            $leaf = $this->findEmptyLeaf($childNode);

            if ($leaf) {
                break;
            }
        }

        if (!$leaf && $tree->children->count() === 1) {
            return $tree;
        }

        if ($leaf) {
            return $leaf;
        }

        return null;
    }

    private function flattenRuleNodes(Collection $items, string $operation): Collection
    {
        if (empty($operation) || $operation === 'NOT') {
            return $items;
        }

        return $items->map(function (Node $node) {
            return $node->ruleNodes($node);
        })->flatten();
    }
}
