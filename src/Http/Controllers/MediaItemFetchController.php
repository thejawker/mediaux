<?php

namespace TheJawker\Mediaux\Http\Controllers;

use TheJawker\Mediaux\DataTransferObjects\ConversionSpecification;
use TheJawker\Mediaux\Models\MediaItem;

class MediaItemFetchController
{
    public function __invoke(MediaItem $mediaItem, string $filename)
    {
        $conversionSpec = ConversionSpecification::fromRequest(request()->instance());
        $conversion = $mediaItem->getConversion($conversionSpec);

        return response($conversion->getContent(), 200, [
            'Content-Type' => $conversion->getMimeType(),
            'Content-Length' => $conversion->getSize(),
            'X-Hash' => $conversion->getHash(),
        ]);
    }

    public function withOptions(MediaItem $mediaItem, string $options, string $filename)
    {
        return self::__invoke($mediaItem, $filename);
    }
}
