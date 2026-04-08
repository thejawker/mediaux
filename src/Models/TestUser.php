<?php

namespace TheJawker\Mediaux\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use TheJawker\Mediaux\Contracts\HasMediaContract;
use TheJawker\Mediaux\Database\Factories\TestUserFactory;
use TheJawker\Mediaux\Traits\HasMedia;

class TestUser extends Authenticatable implements HasMediaContract
{
    /** @use HasFactory<TestUserFactory> */
    use HasFactory, HasMedia;

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
}
