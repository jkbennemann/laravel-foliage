<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Validator\Contracts;

use Illuminate\Support\Collection;
use Jkbennemann\Foliage\Core\Node;
use Jkbennemann\Foliage\Exceptions\RuleValidation;
use Jkbennemann\Foliage\Validator\ValidationDataBuilder;

abstract class ValidationStrategy
{
    protected Collection $validationErrors;

    protected bool $raiseException = true;

    protected bool $isValid = true;

    public function __construct(
        protected ?ValidationDataContract $payloadBuilder = null
    ) {
        if (! $this->payloadBuilder) {
            $this->payloadBuilder = new ValidationDataBuilder();
        }

        $this->validationErrors = new Collection();
    }

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
        return $this->validationErrors
            ->filter()
            ->unique(function (RuleValidation $error) {
                return $error->failedRule()?->normalizedKey() ?: 'generic';
            });
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @throws RuleValidation
     */
    abstract public function evaluateNode(Node $node, array $payload, ?Node $parent);
}
