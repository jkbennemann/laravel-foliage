<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Validator\Contracts;

use Illuminate\Support\Collection;
use Jkbennemann\BusinessRequirements\Core\Node;
use Jkbennemann\BusinessRequirements\Exceptions\RuleValidation;

abstract class BaseValidator
{
    protected Collection $validationErrors;

    public function __construct(
        protected readonly ValidationDataContract $payloadBuilder,
        protected bool $raiseException = true
    ) {
        $this->validationErrors = new Collection();
    }

    abstract public function evaluate(Node $rootNode, array $payload): bool;

    public function setRaiseException(bool $raiseException): self
    {
        $this->raiseException = $raiseException;

        return $this;
    }

    public function withoutExceptions(): self
    {
        $this->raiseException = false;

        return $this;
    }

    public function withExceptions(): self
    {
        $this->raiseException = true;

        return $this;
    }

    public function errors(): Collection
    {
        return $this->validationErrors->unique(function (RuleValidation $error) {
            return $error->failedRule()->normalizedKey();
        });
    }
}
