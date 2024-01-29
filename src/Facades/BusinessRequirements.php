<?php

declare(strict_types=1);

namespace Jkbennemann\BusinessRequirements\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jkbennemann\BusinessRequirements\BusinessRequirements
 */
class BusinessRequirements extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Jkbennemann\BusinessRequirements\BusinessRequirements::class;
    }
}
