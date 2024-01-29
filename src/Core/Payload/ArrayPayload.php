<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core\Payload;

use Jkbennemann\BusinessRequirements\Core\Contracts\ValidationPayloadContract;

class ArrayPayload implements ValidationPayloadContract
{
    public function __construct(private readonly array $data)
    {
    }

    public function getData(): array
    {
        return $this->data;
    }
}
