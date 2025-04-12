<?php

namespace TheJawker\Mediaux\Http\Controllers;

use Illuminate\Http\Request;
use TheJawker\Mediaux\Mediaux;

class MediaUploadController
{
    public function __invoke(Request $request)
    {
        return Mediaux::upload($request)
            ->respond();
    }
}
