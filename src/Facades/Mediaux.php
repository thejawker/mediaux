<?php

namespace Bram Veerman\Mediaux\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Bram Veerman\Mediaux\Mediaux
 */
class Mediaux extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Bram Veerman\Mediaux\Mediaux::class;
    }
}
