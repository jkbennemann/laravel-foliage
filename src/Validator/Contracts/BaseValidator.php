<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Validator\Contracts;

use Illuminate\Support\Collection;
use Jkbennemann\BusinessRequirements\Core\Node;

abstract class BaseValidator
{
    protected Collection $validationErrors;

    public function __construct(
        protected readonly ValidationDataContract $payloadBuilder
    ) {
        $this->validationErrors = new Collection();
    }

    abstract public function evaluate(Node $rootNode, array $payload): void;
}
