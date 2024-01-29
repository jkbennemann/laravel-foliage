<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core\Payload;

use DateTimeInterface;
use Jkbennemann\BusinessRequirements\Core\Contracts\ValidationPayloadContract;

class DateAvailabilityPayload implements ValidationPayloadContract
{
    public function __construct(private readonly DateTimeInterface $datetime)
    {
    }

    public function getData(): array
    {
        return [$this->datetime];
    }
}
