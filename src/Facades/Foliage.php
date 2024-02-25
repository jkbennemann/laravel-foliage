<?php

declare(strict_types=1);

namespace Jkbennemann\Foliage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jkbennemann\Foliage\Foliage
 */
class Foliage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Jkbennemann\Foliage\Foliage::class;
    }
}
