<?php

namespace TheJawker\Mediaux\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use TheJawker\Mediaux\Database\Factories\TestUserFactory;

class TestUser extends Authenticatable
{
    /** @use HasFactory<TestUserFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'email',
        'remember_token',
    ];

    public function mediaItems(): HasMany
    {
        return $this->hasMany(MediaItem::class, 'user_id', 'id');
    }
}
