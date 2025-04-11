<?php

use App\Models\MediaItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\postJson;

test('a user can upload a media file', function () {
    $user = User::factory()->create();
    $image = new UploadedFile(
        fixtures('test.jpg'),
        'test.jpg',
        'image/jpeg',
        null,
        true
    );

    auth()->login($user);

    $response = postJson('/api/media', [
        'file' => $image,
    ]);

    $response
        ->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'hash',
            'filename',
            'original_filename',
            'mime_type',
            'public',
            'created_at',
            'updated_at',
        ]);

    $mediaItem = MediaItem::first();

    expect()
        ->and($mediaItem->id)->toBeInt()
        ->and($mediaItem->filename)->toBeString()
        ->and($mediaItem->original_filename)->toBe('test.jpg')
        ->and($mediaItem->disk)->toBe('public');
});

test('an uploaded asset has an expiration date', function () {
    $user = userFactory()->create();
    $image = new UploadedFile(
        fixtures('test.jpg'),
        'test.jpg',
        'image/jpeg',
        null,
        true
    );

    auth()->login($user);

    postJson('/api/media', [
        'file' => $image,
    ])->assertCreated();

    $mediaItem = MediaItem::first();

    expect($mediaItem->expires_at)->isAfter(now()->addMinutes(59));
});
