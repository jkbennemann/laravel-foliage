<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Facades;

use Illuminate\Support\Facades\Facade;

class Rule extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Jkbennemann\Foliage\Core\Rule::class;
    }
}
