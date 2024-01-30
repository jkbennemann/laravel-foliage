<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core\Payload;

use DateTimeInterface;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class DateAvailabilityPayloadBase extends BaseValidationPayload
{
    public function __construct(
        #[WithTransformer(DateTimeInterfaceTransformer::class)]
        public readonly DateTimeInterface $from,
        #[WithTransformer(DateTimeInterfaceTransformer::class)]
        public readonly ?DateTimeInterface $until
    ) {
    }
}
