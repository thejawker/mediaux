<?php

use App\Models\MediaConversion;
use App\Models\MediaItem;

use function Pest\Laravel\get;

beforeEach(function () {
    Storage::fake('public');
});

test('it can serve a media item that is an image', function () {
    $image = file_get_contents(base_path('tests/fixtures/test.jpg'));
    $mediaItem = MediaItem::factory()->withContents(disk: 'public', contents: $image, filename: 'test.jpg')->create([
        'public' => true,
    ]);

    // run the get request
    $response = get("/media/{$mediaItem->id}/test.jpg");

    // assert we get the correct image
    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'image/jpeg')
        ->assertHeader('Content-Length', strlen($image))
        ->assertHeader('X-Hash', $mediaItem->hash);

    expect(MediaConversion::count())->toBe(0);
});

test('it can dynamically convert the file into a webp', function () {
    $image = file_get_contents(base_path('tests/fixtures/test.jpg'));
    $mediaItem = MediaItem::factory()->withContents(disk: 'public', contents: $image, filename: 'test.jpg')->create([
        'public' => true,
    ]);

    // run the get request
    $response = get("/media/{$mediaItem->id}/test.webp");

    // assert we get the correct image
    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'image/webp');

    // assert that the file is smaller than the original
    expect()
        ->and(strlen($response->getContent()))->toBeLessThan(strlen($image))
        ->and(MediaConversion::count())->toBe(1);
});

// convert to png
test('it can dynamically convert the file into a png', function () {
    $image = file_get_contents(base_path('tests/fixtures/test.jpg'));
    $mediaItem = MediaItem::factory()->withContents(disk: 'public', contents: $image, filename: 'test.jpg')->create([
        'public' => true,
    ]);

    // run the get request
    $response = get("/media/{$mediaItem->id}/test.png");

    // assert we get the correct image
    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'image/png');

    // assert that the file is smaller than the original
    expect()
        ->and(strlen($response->getContent()))->toBeGreaterThan(strlen($image))
        ->and(MediaConversion::count())->toBe(1);
});

test('hitting the conversion endpoint twice will not convert the file again', function () {
    $image = file_get_contents(base_path('tests/fixtures/test.jpg'));
    $mediaItem = MediaItem::factory()->withContents(disk: 'public', contents: $image, filename: 'test.jpg')->create([
        'public' => true,
    ]);

    get("/media/{$mediaItem->id}/test.webp")->assertOk();
    get("/media/{$mediaItem->id}/test.webp")->assertOk();

    expect(MediaConversion::count())->toBe(1);
});
