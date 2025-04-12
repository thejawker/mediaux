<?php

namespace TheJawker\Mediaux\Http\Controllers;

use Illuminate\Http\Request;
use TheJawker\Mediaux\Actions\CreateMediaItemFromRequestAction;

class MediaUploadController
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $mediaItem = (new CreateMediaItemFromRequestAction)->execute(auth()->user(), $request);

        return response()->json($mediaItem, 201);
    }
}
