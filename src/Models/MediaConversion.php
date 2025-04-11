<?php

namespace TheJawker\Mediaux\Models;

use App\Contracts\MediaContract;
use App\DataTransferObjects\ConversionSpecification;
use App\Traits\IsMedia;
use Database\Factories\MediaConversionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;

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
