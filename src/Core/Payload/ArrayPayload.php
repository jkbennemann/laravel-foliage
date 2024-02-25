<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Core\Payload;

class ArrayPayload extends BaseValidationPayload
{
    private array $localData;

    public function __construct($data)
    {
        $this->localData = [...$data];
    }

    public function toArray(): array
    {
        return $this->localData;
    }
}
