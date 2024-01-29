<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core\Contracts;

interface ValidationPayloadContract
{
    public function getDatetime(): array;
}
