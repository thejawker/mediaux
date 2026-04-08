<?php

use Illuminate\Http\Request;
use TheJawker\Mediaux\Models\MediaItem;

return [
    'user_model' => 'App\\Models\\User',

    'disable_routes' => false,

    'get_url' => function (Request $request, MediaItem $mediaItem): string {
        return route('media.fetch', [$mediaItem, $mediaItem->original_filename]);
    },
];
