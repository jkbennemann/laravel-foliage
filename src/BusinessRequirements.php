<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements;

use Jkbennemann\BusinessRequirements\Core\Node;
use Jkbennemann\BusinessRequirements\Core\TreeBuilder;
use Jkbennemann\BusinessRequirements\Validator\Contracts\BaseValidator;
use Jkbennemann\BusinessRequirements\Validator\Result;

readonly class BusinessRequirements
{
    public function __construct(
        private BaseValidator $validator,
        private TreeBuilder $builder
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
