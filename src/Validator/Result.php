<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Validator;

use Illuminate\Support\Collection;

class Result
{
    public function __construct(
        private readonly bool $isValid,
        private readonly Collection $errors
    ) {
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function errors(): Collection
    {
        return $this->errors;
    }
}
