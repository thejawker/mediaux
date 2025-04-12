<?php

namespace TheJawker\Mediaux\Http\Controllers;

use TheJawker\Mediaux\Mediaux;

class MediaItemFetchController
{
    public function __invoke()
    {
        return Mediaux::fetcher()
            ->respond();
    }
}
