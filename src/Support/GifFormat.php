<?php

namespace TheJawker\Mediaux\Support;

use FFMpeg\Format\Video\DefaultVideo;

class GifFormat extends DefaultVideo
{
    public function __construct()
    {
        $this->setVideoCodec('gif');
    }

    public function getAvailableAudioCodecs(): array
    {
        return [];
    }

    public function getAvailableVideoCodecs(): array
    {
        return ['gif'];
    }

    public function supportBFrames()
    {
        return false;
    }
}
