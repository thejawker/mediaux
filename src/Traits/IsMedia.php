<?php

namespace TheJawker\Mediaux\Traits;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

trait IsMedia
{
    public function getFileExtension(): string
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    public function getSize(): int
    {
        return $this->getDisk()->size($this->filename);
    }

    public function getMimeType(): string
    {
        return $this->getDisk()->mimeType($this->filename);
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getContent(): ?string
    {
        return $this->getDisk()->get($this->filename);
    }

    public function getDisk(): Filesystem
    {
        return Storage::disk($this->disk);
    }

    public function getTemporaryFilePath(): string
    {
        $contents = $this->getContent();
        $tempPath = tempnam(sys_get_temp_dir(), 'media');

        file_put_contents($tempPath, $contents);

        return $tempPath;
    }

    public function getInternalFileName(): string
    {
        return $this->filename;
    }

    public function deleteFile(): void
    {
        $this->getDisk()->delete($this->filename);
    }

    public function getUrl(): string
    {
        return route('media.fetch', [$this, $this->original_filename]);
    }
}
