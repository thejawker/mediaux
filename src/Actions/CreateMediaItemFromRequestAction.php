<?php

namespace TheJawker\Mediaux\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Storage;
use Str;

class CreateMediaItemFromRequestAction
{
    const DISK = 'public';

    public function execute(Authenticatable $user, Request $request)
    {
        $uuid = Str::uuid();
        $filename = $request->file('file')->getClientOriginalName();
        $contents = $request->file('file')->get();
        $newFilename = $uuid.'.'.pathinfo($filename, PATHINFO_EXTENSION);
        Storage::disk(self::DISK)->put($newFilename, $contents);

        return $user->mediaItems()->create([
            'filename' => $newFilename,
            'original_filename' => $filename,
            'mime_type' => $request->file('file')->getMimeType(),
            'disk' => self::DISK,
            'hash' => (new HashMediaAction)->execute($contents),
            'public' => true,
            'expires_at' => now()->addHour(),
        ]);
    }
}
