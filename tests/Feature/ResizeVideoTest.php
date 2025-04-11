<?php

use App\Models\MediaItem;

use function Pest\Laravel\get;

beforeEach(function () {
    Storage::fake('public');
});

test('it can resize an video on the fly based on width', function () {
    $video = file_get_contents(fixtures('test.mp4'));
    $mediaItem = MediaItem::factory()->withContents(disk: 'public', contents: $video, filename: 'test.mp4')->create([
        'public' => true,
    ]);

    // run the get request
    $response = get("/media/{$mediaItem->id}/w_300/test.mp4");

    // assert we get the correct video
    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'video/mp4');

    $conversion = $mediaItem->conversions()->first();

    $response->assertHeader('X-Hash', $conversion->getHash());

    $size = getVideoDims($conversion->getTemporaryFilePath());
    expect()
        ->and($size->getWidth())->toBeBetween(300 - 3, 300 + 3);
})->group('ffmpeg');

test('it can resize an video on the fly based on height', function () {
    $video = file_get_contents(fixtures('test.mp4'));
    $mediaItem = MediaItem::factory()->withContents(disk: 'public', contents: $video, filename: 'test.mp4')->create([
        'public' => true,
    ]);

    // run the get request
    $response = get("/media/{$mediaItem->id}/h_200/test.mp4");

    // assert we get the correct video
    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'video/mp4');

    $conversion = $mediaItem->conversions()->first();

    $response->assertHeader('X-Hash', $conversion->getHash());

    $size = getVideoDims($conversion->getTemporaryFilePath());
    expect()
        ->and($size->getHeight())->toBeBetween(200 - 3, 200 + 3);
})->group('ffmpeg');

test('it can resize an video on the fly based on width and height', function () {
    $video = file_get_contents(fixtures('test.mp4'));
    $mediaItem = MediaItem::factory()->withContents(disk: 'public', contents: $video, filename: 'test.mp4')->create([
        'public' => true,
    ]);

    $size = getVideoDims($mediaItem->getTemporaryFilePath());
    $width = $size->getWidth() / 2;
    $height = $size->getHeight() / 2;

    // run the get request
    $response = get("/media/{$mediaItem->id}/w_{$width},h_{$height}/test.mp4");

    // assert we get the correct video
    $response
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'video/mp4');

    $conversion = $mediaItem->conversions()->first();

    $response->assertHeader('X-Hash', $conversion->getHash());

    $size = getVideoDims($conversion->getTemporaryFilePath());
    expect()
        ->and($size->getWidth())->toBeBetween($width - 3, $width + 3)
        ->and($size->getHeight())->toBeBetween($height - 3, $height + 3);
})->group('ffmpeg');

function getVideoDims($path)
{
    // use ffmpeg to get the dimensions of the video
    return FFMpeg::getFFProbe()->streams($path)->videos()->first()->getDimensions();
}
