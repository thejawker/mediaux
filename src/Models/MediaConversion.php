<?php

namespace TheJawker\Mediaux\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;
use TheJawker\Mediaux\Contracts\MediaContract;
use TheJawker\Mediaux\Database\Factories\MediaConversionFactory;
use TheJawker\Mediaux\Traits\IsMedia;

class MediaConversion extends Model implements MediaContract
{
    /** @use HasFactory<MediaConversionFactory> */
    use HasFactory, IsMedia;

    protected static $unguarded = true;

    public function getSpecifications(): ConversionSpecification
    {
        return ConversionSpecification::fromArray($this->specifications);
    }

    protected function casts(): array
    {
        return [
            'specifications' => 'array',
        ];
    }

    public function deleteWithAsset(): void
    {
        Storage::disk($this->disk)->delete($this->filename);
        $this->delete();
    }
}
