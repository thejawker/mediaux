<?php

namespace TheJawker\Mediaux\Actions;

use Illuminate\Http\Request;
use Storage;
use Str;
use TheJawker\Mediaux\Contracts\HasMediaContract;

class CreateMediaItemFromRequestAction
{
    const DISK = 'public';

    public function execute(HasMediaContract $user, Request $request, ?bool $private = null)
    {
        $uuid = Str::uuid();
        $filename = $request->file('file')->getClientOriginalName();
        $contents = $request->file('file')->get();
        $newFilename = $uuid.'.'.pathinfo($filename, PATHINFO_EXTENSION);
        Storage::disk(self::DISK)->put($newFilename, $contents);

        $private = $private ?? (bool) config('mediaux.private_by_default');

        return $user->mediaItems()->create([
            'filename' => $newFilename,
            'original_filename' => $filename,
            'mime_type' => $request->file('file')->getMimeType(),
            'disk' => self::DISK,
            'hash' => (new HashMediaAction)->execute($contents),
            'public' => ! $private,
            'expires_at' => now()->addHour(),
        ]);
    }
}
