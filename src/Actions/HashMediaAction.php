<?php

namespace TheJawker\Mediaux\Actions;

use Jenssegers\ImageHash\Hash;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\PerceptualHash;
use TheJawker\Mediaux\Models\MediaConversion;

class HashMediaAction
{
    public function execute(string $contents): string
    {
        if ($this->isImage($contents)) {
            return $this->hashImage($contents);
        }

        return md5($contents);
    }

    private function isImage(string $contents): bool
    {
        // check if mime type is image
        if (str_starts_with($contents, 'image/')) {
            return true;
        }

        return false;
    }

    public function hashImage(string $contents): Hash
    {
        $hasher = new ImageHash(new PerceptualHash);

        return $hasher->hash($contents);
    }

    public function hashMedia(MediaConversion $conversion): string
    {
        return md5_file($conversion->getTemporaryFilePath());
    }
}
