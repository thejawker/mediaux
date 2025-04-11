<?php

use App\Models\MediaItem;
use function Pest\Laravel\get;

beforeEach(function () {
    Storage::fake('public');
});

test('it can retrieves a video', function () {
    $video = file_get_contents(base_path('tests/fixtures/test.mp4'));
    $mediaItem = MediaItem::factory()->withContents(disk: 'public', contents: $video, filename: 'test.mp4')->create([
        'public' => true,
    ]);

    // run the get request
    $response = get("/media/{$mediaItem->id}/test.mp4");

    // assert we get the correct video
    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'video/mp4')
        ->assertHeader('Content-Length', strlen($video))
        ->assertHeader('X-Hash', $mediaItem->hash);
})->group('ffmpeg');

test('it can convert a video into a gif', function () {
    $video = file_get_contents(base_path('tests/fixtures/test.mp4'));
    $mediaItem = MediaItem::factory()->withContents(disk: 'public', contents: $video, filename: 'test.mp4')->create([
        'public' => true,
    ]);

    // run the get request
    $response = get("/media/{$mediaItem->id}/test.gif");

    // assert we get the correct video
    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'image/gif');
})->group('ffmpeg');

test('it can convert a video into a webm', function () {
    $video = file_get_contents(base_path('tests/fixtures/test.mp4'));
    $mediaItem = MediaItem::factory()->withContents(disk: 'public', contents: $video, filename: 'test.mp4')->create([
        'public' => true,
    ]);

    // run the get request
    $response = get("/media/{$mediaItem->id}/test.webm");

    // assert we get the correct video
    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'video/webm');
})->group('ffmpeg');