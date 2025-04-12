<?php

namespace TheJawker\Mediaux\Models;

use Str;
use TheJawker\Mediaux\Actions\ConvertMediaAction;
use TheJawker\Mediaux\Traits\IsMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use TheJawker\Mediaux\Contracts\MediaContract;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use TheJawker\Mediaux\Database\Factories\MediaItemFactory;
use TheJawker\Mediaux\DataTransferObjects\ConversionSpecification;

class MediaItem extends Model implements MediaContract
{
    /** @use HasFactory<MediaItemFactory> */
    use HasFactory, IsMedia;

    protected static $unguarded = true;

    protected $appends = [
        'url',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('mediaux.user_model'), 'id', 'user_id');
    }

    public function getConversion(ConversionSpecification $conversionSpec): MediaContract
    {
        if ($this->getFileExtension() === $conversionSpec->fileExtension && count($conversionSpec->toArray()) === 1) {
            return $this;
        }

        $conversion = $this->conversions()
            ->whereJsonContains('specifications->file_extension', $conversionSpec->fileExtension)
            ->when($conversionSpec->height, fn($query) => $query->whereJsonContains('specifications->height', $conversionSpec->height))
            ->when($conversionSpec->width, fn($query) => $query->whereJsonContains('specifications->width', $conversionSpec->width))
            ->first();

        if ($conversion) {
            return $conversion;
        }

        return $this->convertTo($conversionSpec);
    }

    public function convertTo(ConversionSpecification $conversionSpec): MediaContract
    {
        return (new ConvertMediaAction)->execute($this, $conversionSpec);
    }

    public function prepareConversion(ConversionSpecification $specification)
    {
        $uuid = Str::uuid();

        return $this->conversions()->make([
            'filename' => $uuid . '.' . $specification->fileExtension,
            'original_filename' => $this->original_filename,
            'disk' => $this->disk,
            'specifications' => $specification->toArray(),
        ]);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(MediaConversion::class);
    }

    public function scopeExpired(Builder $query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function deleteWithDependencies(): void
    {
        $this->conversions->each(fn(MediaConversion $conversion) => $conversion->deleteWithAsset());
        $this->deleteFile();

        $this->delete();
    }

    public function markAsInUse(): void
    {
        $this->update([
            'expires_at' => null,
        ]);
    }

    public function isUsed(): bool
    {
        return $this->expires_at === null;
    }

    public function getUrlAttribute(): string
    {
        return $this->getUrl();
    }
}
