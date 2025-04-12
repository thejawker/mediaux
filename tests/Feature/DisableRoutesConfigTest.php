<?php

use TheJawker\Mediaux\Models\MediaItem;

use TheJawker\Mediaux\Tests\DisableRoutesTrait;
use function Pest\Laravel\get;

pest()->extends(DisableRoutesTrait::class)->in('.');

test('will not register routes when disabled', function () {
})->todo();
