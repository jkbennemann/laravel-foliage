<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Facades;

use Illuminate\Support\Facades\Facade;

class Rule extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Jkbennemann\BusinessRequirements\Core\Rule::class;
    }
}
