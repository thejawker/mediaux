<?php

namespace TheJawker\Mediaux\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use TheJawker\Mediaux\Models\MediaItem;

/**
 * @mixin Model
 */
trait HasMedia
{
    /**
     * @return MorphToMany<MediaItem>|HasMany<MediaItem>
     */
    public function mediaItems()
    {
        $userModel = config('mediaux.user_model');

        if ($userModel && get_class($this) === $userModel) {
            return $this->hasMany(MediaItem::class, 'user_id', 'id');
        }

        return $this->morphToMany(MediaItem::class, 'mediable');
    }
}
