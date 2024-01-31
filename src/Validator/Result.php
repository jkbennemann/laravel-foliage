<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Validator;

use Illuminate\Support\Collection;

readonly class Result
{
    public function __construct(
        private bool $isValid,
        private Collection $errors
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
