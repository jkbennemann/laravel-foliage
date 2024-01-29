<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Validator\Contracts;

use Jkbennemann\BusinessRequirements\Core\Node;

interface Validator
{
    public function evaluate(Node $rootNode, array $payload): void;
}
