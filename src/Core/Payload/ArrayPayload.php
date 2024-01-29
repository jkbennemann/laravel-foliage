<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core\Payload;

use Jkbennemann\BusinessRequirements\Core\Contracts\ValidationPayloadContract;

readonly class ArrayPayload implements ValidationPayloadContract
{
    public function __construct(private array $data)
    {
    }

    public function getDatetime(): array
    {
        return $this->data;
    }
}
