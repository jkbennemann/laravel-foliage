<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core\Payload;

use DateTimeInterface;
use Jkbennemann\BusinessRequirements\Core\Contracts\ValidationPayloadContract;

readonly class DateAvailabilityPayload implements ValidationPayloadContract
{
    public function __construct(private DateTimeInterface $datetime)
    {
    }

    public function getDatetime(): array
    {
        return [$this->datetime];
    }
}
