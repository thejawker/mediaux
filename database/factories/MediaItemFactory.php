<?php

namespace TheJawker\Mediaux\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Storage;
use Str;
use TheJawker\Mediaux\Actions\HashMediaAction;
use TheJawker\Mediaux\Models\MediaItem;

/**
 * @extends Factory<MediaItem>
 */
class MediaItemFactory extends Factory
{
    protected $model = MediaItem::class;

    public function definition(): array
    {
        $user = config('mediaux.user_model');

        return [
            'user_id' => $user::factory(),
        ];
    }

    public function withContents($disk, $contents, string $filename): MediaItemFactory|Factory
    {
        $uuid = Str::uuid();
        // store the image on the disk storage
        $newFilename = $uuid.'.'.pathinfo($filename, PATHINFO_EXTENSION);
        Storage::disk($disk)->put($newFilename, $contents);

        return $this->state(fn (array $attributes) => [
            'filename' => $newFilename,
            'mime_type' => Storage::disk($disk)->mimeType($newFilename),
            'disk' => $disk,
            'original_filename' => $filename,
            'hash' => (new HashMediaAction)->execute($contents),
        ]);
    }

    public function withFile(?string $path = null): MediaItemFactory|Factory
    {
        $path = $path ?: base_path('tests/fixtures/test.jpg');
        $contents = file_get_contents($path);
        $filename = pathinfo($path, PATHINFO_BASENAME);

        return $this->withContents('public', $contents, $filename);
    }
}
