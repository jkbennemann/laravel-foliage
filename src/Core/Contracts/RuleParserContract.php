<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Core\Contracts;

use Jkbennemann\Foliage\Core\BaseValidationRule;

interface RuleParserContract
{
    public function parse(?string $ruleName): ?BaseValidationRule;
}
