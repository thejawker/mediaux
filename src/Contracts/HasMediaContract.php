<?php

namespace TheJawker\Mediaux\Contracts;

use Illuminate\Database\Eloquent\Relations\Relation;

interface HasMediaContract
{
    public function mediaItems(): Relation;
}
