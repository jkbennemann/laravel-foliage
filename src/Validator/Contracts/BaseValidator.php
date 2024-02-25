<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Validator\Contracts;

use Illuminate\Support\Collection;
use Jkbennemann\BusinessRequirements\Core\Node;

abstract class BaseValidator
{
    public function __construct(
        protected readonly ValidationDataContract $payloadBuilder,
        protected readonly ValidationStrategy $strategy
    ) {
    }

    abstract public function evaluate(Node $rootNode, array $payload): bool;

    public function setRaiseException(bool $raiseException): self
    {
        $this->strategy->setRaiseException($raiseException);

        return $this;
    }

    public function withoutExceptions(): self
    {
        $this->strategy->withoutExceptions();

        return $this;
    }

    public function withExceptions(): self
    {
        $this->strategy->withExceptions();

        return $this;
    }

    public function errors(): Collection
    {
        return $this->strategy->errors();
    }

    public function isValid(): bool
    {
        return $this->strategy->isValid();
    }
}
