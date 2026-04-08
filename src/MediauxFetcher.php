<?php

namespace TheJawker\Mediaux;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TheJawker\Mediaux\DataTransferObjects\ConversionSpecification;
use TheJawker\Mediaux\Models\MediaItem;

class MediauxFetcher
{
    private ?MediaItem $mediaItem = null;

    public function __construct(public Request $request)
    {
    }

    public function respond(): Application|ResponseFactory|Response
    {
        $conversionSpec = ConversionSpecification::fromRequest($this->request->instance());
        $mediaItem = $this->mediaItem ?? MediaItem::findOrFail(request()->route('mediaItem'));

        abort_unless(
            (bool) config('mediaux.authorize')($this->request, $mediaItem),
            403,
        );

        $conversion = $mediaItem->getConversion($conversionSpec);

        if (request()->headers->get('If-None-Match') === $conversion->getHash()) {
            return response()->noContent(304);
        }

        return response($conversion->getContent(), 200, [
            'Content-Type' => $conversion->getMimeType(),
            'Content-Length' => $conversion->getSize(),
            'X-Hash' => $conversion->getHash(),
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'ETag' => $conversion->getHash(),
        ]);
    }

    public function mediaItem(Models\MediaItem $mediaItem): static
    {
        $this->mediaItem = $mediaItem;
        return $this;
    }
}
