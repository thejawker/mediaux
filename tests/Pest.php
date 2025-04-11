<?php

use TheJawker\Mediaux\Database\Factories\TestUserFactory;
use TheJawker\Mediaux\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function fixtures(string $path)
{
    return __DIR__.'/Feature/fixtures'.$path;
}

/**
 * @template TFactory of \Illuminate\Database\Eloquent\Factories\Factory
 *
 * @return TestUserFactory
 */
function userFactory()
{
    return config('mediaux.user_model')->factory();
}
