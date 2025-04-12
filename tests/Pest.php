<?php

use TheJawker\Mediaux\Database\Factories\TestUserFactory;
use TheJawker\Mediaux\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function fixtures(string $path)
{
    return __DIR__.'/fixtures/'.\Illuminate\Support\Str::ltrim($path, '/');
}

/**
 * @template TFactory of \Illuminate\Database\Eloquent\Factories\Factory
 *
 * @return TestUserFactory
 */
function userFactory()
{
    $userModel = config('mediaux.user_model');

    return $userModel::factory();
}
