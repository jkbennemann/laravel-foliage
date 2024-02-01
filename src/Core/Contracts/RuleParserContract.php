<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Core\Contracts;

use Jkbennemann\BusinessRequirements\Core\BaseValidationRule;

interface RuleParserContract
{
    public function parse(?string $ruleName): ?BaseValidationRule;
}
