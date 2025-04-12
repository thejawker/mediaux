<?php

namespace TheJawker\Mediaux\DataTransferObjects;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ConversionSpecification
{
    public function __construct(
        public string $fileExtension,
        public ?int $height = null,
        public ?int $width = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $filename = $request->route('filename');
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $height = self::getOption($request->route('options'), 'h_');
        $width = self::getOption($request->route('options'), 'w_');

        return new self(
            fileExtension: $extension,
            height: $height,
            width: $width,
        );
    }

    private static function getOption(?string $options, string $key)
    {
        if (! $options) {
            return null;
        }

        $parts = explode(',', $options);
        foreach ($parts as $part) {
            if (str_starts_with($part, $key)) {
                return str_replace($key, '', $part);
            }
        }
    }

    public function toArray(): array
    {
        return array_filter([
            'file_extension' => $this->fileExtension,
            'height' => $this->height,
            'width' => $this->width,
        ], fn ($value) => $value !== null);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            fileExtension: Arr::get($data, 'file_extension'),
            height: Arr::get($data, 'height'),
            width: Arr::get($data, 'width'),
        );
    }
}
