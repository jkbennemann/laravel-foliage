<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage;

use Jkbennemann\Foliage\Core\Node;
use Jkbennemann\Foliage\Core\TreeBuilder;
use Jkbennemann\Foliage\Validator\Contracts\BaseValidator;
use Jkbennemann\Foliage\Validator\Result;

class Foliage
{
    public function __construct(
        private readonly BaseValidator $validator,
        private readonly TreeBuilder $builder
    ) {
    }

    public function buildTree(string|array $treeStructure): Node
    {
        if (is_string($treeStructure)) {
            $treeStructure = json_decode($treeStructure, true);
        }

        return $this->builder->build($treeStructure);
    }

    public function validate(Node $node, array $payload): void
    {
        $this->validator
            ->withExceptions()
            ->evaluate($node, $payload);
    }

    public function validateSilently(Node $node, array $payload): Result
    {
        $isValid = $this->validator
            ->withoutExceptions()
            ->evaluate($node, $payload);

        return new Result($isValid, $this->validator->errors());
    }
}
