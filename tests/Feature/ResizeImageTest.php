<?php

use App\Models\MediaItem;

use function Pest\Laravel\get;

beforeEach(function () {
    Storage::fake('public');
});

test('it can resize an image on the fly based on width', function () {
    $image = file_get_contents(base_path('tests/fixtures/test.jpg'));
    $mediaItem = MediaItem::factory()->withContents(disk: 'public', contents: $image, filename: 'test.jpg')->create([
        'public' => true,
    ]);

    // run the get request
    $response = get("/media/{$mediaItem->id}/w_300/test.jpg");

    // assert we get the correct image
    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'image/jpeg');

    $conversion = $mediaItem->conversions()->first();

    $response->assertHeader('X-Hash', $conversion->getHash());

    $size = getimagesize($conversion->getTemporaryFilePath());
    expect()
        ->and($size[0])->toBe(300);
});

test('it can resize an image on the fly based on height', function () {
    $image = file_get_contents(base_path('tests/fixtures/test.jpg'));
    $mediaItem = MediaItem::factory()->withContents(disk: 'public', contents: $image, filename: 'test.jpg')->create([
        'public' => true,
    ]);

    // run the get request
    $response = get("/media/{$mediaItem->id}/h_300/test.jpg");

    // assert we get the correct image
    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'image/jpeg');

    $conversion = $mediaItem->conversions()->first();

    $response->assertHeader('X-Hash', $conversion->getHash());

    $size = getimagesize($conversion->getTemporaryFilePath());
    expect()
        ->and($size[1])->toBe(300);
});

test('it can resize an image on the fly based on width and height', function () {
    $image = file_get_contents(base_path('tests/fixtures/test.jpg'));
    $mediaItem = MediaItem::factory()->withContents(disk: 'public', contents: $image, filename: 'test.jpg')->create([
        'public' => true,
    ]);
    $sizes = getimagesize(base_path('tests/fixtures/test.jpg'));
    $width = $sizes[0] / 2;
    $height = $sizes[1] / 2;

    // run the get request
    $response = get("/media/{$mediaItem->id}/w_{$width},h_{$height}/test.jpg");

    // assert we get the correct image
    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'image/jpeg');

    $conversion = $mediaItem->conversions()->first();

    $response->assertHeader('X-Hash', $conversion->getHash());

    $size = getimagesize($conversion->getTemporaryFilePath());
    expect()
        ->and($size[0])->toBe($width)
        ->and($size[1])->toBe($height);
});
