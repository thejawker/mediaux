<?php

use App\DataTransferObjects\ConversionSpecification;
use App\Models\MediaItem;
use Illuminate\Database\Eloquent\Factories\Sequence;

beforeEach(function () {
    Storage::fake('public');
});

test('an expired media item is removed from the database', function () {
    $image = file_get_contents(base_path('tests/fixtures/test.jpg'));
    $mediaItem = MediaItem::factory()
        ->withContents(disk: 'public', contents: $image, filename: 'test.jpg')
        ->create([
            'public' => true,
        ]);

    $mediaItem->update([
        'expires_at' => now()->subDay(),
    ]);

    expect(MediaItem::count())->toBe(1);

    Artisan::call('app:clean-expired-media');

    expect(MediaItem::count())->toBe(0);
});

test('delete will actually delete the file too', function () {
    $image = file_get_contents(base_path('tests/fixtures/test.jpg'));
    $mediaItem = MediaItem::factory()
        ->withContents(disk: 'public', contents: $image, filename: 'test.jpg')
        ->create([
            'public' => true,
        ]);

    $mediaItem->update([
        'expires_at' => now()->subDay(),
    ]);

    expect(MediaItem::count())->toBe(1);

    Artisan::call('app:clean-expired-media');

    expect()
        ->and(MediaItem::count())->toBe(0)
        ->and(Storage::disk('public')->exists($mediaItem->filename))->toBeFalse();
});

test('will also delete any conversions', function () {
    $image = file_get_contents(base_path('tests/fixtures/test.jpg'));
    $mediaItem = MediaItem::factory()
        ->withContents(disk: 'public', contents: $image, filename: 'test.jpg')
        ->create([
            'public' => true,
        ]);

    $mediaItem->update([
        'expires_at' => now()->subDay(),
    ]);

    $conversion = $mediaItem->getConversion(ConversionSpecification::fromArray([
        'file_extension' => 'webp',
    ]));

    $conversion->save();

    expect()
        ->and(MediaItem::count())->toBe(1)
        ->and($mediaItem->conversions()->count())->toBe(1);

    Artisan::call('app:clean-expired-media');

    expect()
        ->and(MediaItem::count())->toBe(0)
        ->and($mediaItem->conversions()->count())->toBe(0);
});

test('will not delete non expired media items', function () {
    $image = file_get_contents(base_path('tests/fixtures/test.jpg'));
    MediaItem::factory()
        ->withContents(disk: 'public', contents: $image, filename: 'test.jpg')
        ->count(2)
        ->state(new Sequence(
            ['expires_at' => null],
            ['expires_at' => now()->addDay()],
        ))
        ->create([
            'public' => true,
        ]);

    expect(MediaItem::count())->toBe(2);

    Artisan::call('app:clean-expired-media');

    expect(MediaItem::count())->toBe(2);
});
