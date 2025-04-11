<?php

namespace TheJawker\Mediaux\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \TheJawker\Mediaux\Mediaux
 */
class Mediaux extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \TheJawker\Mediaux\Mediaux::class;
    }
}
