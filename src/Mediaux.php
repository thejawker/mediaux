<?php

namespace TheJawker\Mediaux;

use Illuminate\Http\Request;

class Mediaux
{
    public static function upload(?Request $request = null): MediauxUploader
    {
        return new MediauxUploader($request ?? request());
    }

    public static function fetcher(?Request $request = null): MediauxFetcher
    {
        return new MediauxFetcher($request ?? request());
    }
}
