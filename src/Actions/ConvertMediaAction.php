<?php

namespace TheJawker\Mediaux\Actions;

use Exception;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\FormatInterface;
use FFMpeg\Format\Video\Ogg;
use FFMpeg\Format\Video\WebM;
use FFMpeg\Format\Video\X264;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\Image\Image;
use Str;
use TheJawker\Mediaux\Contracts\MediaContract;
use TheJawker\Mediaux\DataTransferObjects\ConversionSpecification;
use TheJawker\Mediaux\Models\MediaConversion;
use TheJawker\Mediaux\Models\MediaItem;
use TheJawker\Mediaux\Support\GifFormat;

class ConvertMediaAction
{
    public function execute(MediaItem $media, ConversionSpecification $specification): MediaContract
    {
        $conversion = $media->prepareConversion($specification);

        if ($this->isImage($media)) {
            return $this->convertImage($conversion, $media);
        } else {
            return $this->convertVideo($conversion, $media);
        }
    }

    private function isImage(MediaItem $media): bool
    {
        return Str::startsWith($media->getMimeType(), 'image/');
    }

    private function convertImage(MediaConversion $conversion, MediaItem $media): MediaContract
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'media');
        $specification = $conversion->getSpecifications();

        $instance = Image::load($media->getTemporaryFilePath())
            ->format($specification->fileExtension);

        if ($specification->width) {
            $instance->width($specification->width);
        }

        if ($specification->height) {
            $instance->height($specification->height);
        }

        $instance->optimize();

        $instance->save($tempPath);

        $media->getDisk()->put($conversion->filename, file_get_contents($tempPath));

        $conversion->hash = (new HashMediaAction)->hashImage($tempPath);
        $conversion->save();

        return $conversion;
    }

    private function convertVideo(MediaConversion $conversion, MediaItem $media): MediaContract
    {
        $specifications = $conversion->getSpecifications();

        $format = $this->getFormat($specifications);

        // using laravel ffmpeg
        $video = FFMpeg::fromDisk($media->getDisk())
            ->open($media->getInternalFileName());

        $dimensions = $video->getVideoStream()->getDimensions();

        $mode = $specifications->height ? ResizeFilter::RESIZEMODE_SCALE_WIDTH : ResizeFilter::RESIZEMODE_SCALE_HEIGHT;

        $exporter = $video->export()->inFormat($format);

        $exporter->resize(
            width: $specifications->width ?? $dimensions->getWidth(),
            height: $specifications->height ?? $dimensions->getHeight(),
            mode: $mode,
        );

        $exporter->save($conversion->filename);

        $conversion->hash = (new HashMediaAction)->hashMedia($conversion);
        $conversion->save();

        return $conversion;
    }

    /**
     * @throws Exception
     */
    private function getFormat(ConversionSpecification $specifications): FormatInterface
    {
        switch ($specifications->fileExtension) {
            case 'mp4':
                return new X264('aac');
            case 'webm':
                return new WebM;
            case 'ogg':
                return new Ogg;
            case 'gif':
                return new GifFormat;
            default:
                throw new Exception('Unsupported format');
        }
    }
}
