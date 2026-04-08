<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use TheJawker\Mediaux\Models\MediaItem;

use function Pest\Laravel\get;
use function Pest\Laravel\postJson;

beforeEach(function () {
    Storage::fake('public');
});

test('a private media item cannot be fetched without a signature', function () {
    $mediaItem = MediaItem::factory()
        ->withContents(disk: 'public', contents: file_get_contents(fixtures('test.jpg')), filename: 'test.jpg')
        ->create([
            'public' => false,
        ]);

    get("/media/{$mediaItem->id}/test.jpg")
        ->assertForbidden();
});

test('a private media item can be fetched with a valid token url', function () {
    $mediaItem = MediaItem::factory()
        ->withContents(disk: 'public', contents: file_get_contents(fixtures('test.jpg')), filename: 'test.jpg')
        ->create([
            'public' => false,
        ]);

    $url = $mediaItem->getUrl();

    expect($url)->toContain('token=');

    get($url)
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'image/jpeg');
});

test('an expired token is rejected', function () {
    $mediaItem = MediaItem::factory()
        ->withContents(disk: 'public', contents: file_get_contents(fixtures('test.jpg')), filename: 'test.jpg')
        ->create([
            'public' => false,
        ]);

    config()->set('mediaux.signed_url_ttl', 5);
    $url = $mediaItem->getUrl();

    $this->travel(10)->minutes();

    get($url)
        ->assertForbidden();
});

test('a token minted for one item cannot be used to fetch another item', function () {
    $contents = file_get_contents(fixtures('test.jpg'));

    $itemA = MediaItem::factory()
        ->withContents(disk: 'public', contents: $contents, filename: 'a.jpg')
        ->create(['public' => false]);

    $itemB = MediaItem::factory()
        ->withContents(disk: 'public', contents: $contents, filename: 'b.jpg')
        ->create(['public' => false]);

    $tokenForA = parse_url($itemA->getUrl(), PHP_URL_QUERY);

    get("/media/{$itemB->id}/b.jpg?{$tokenForA}")
        ->assertForbidden();
});

test('the same token grants access to every transformation of the item', function () {
    $mediaItem = MediaItem::factory()
        ->withContents(disk: 'public', contents: file_get_contents(fixtures('test.jpg')), filename: 'test.jpg')
        ->create([
            'public' => false,
        ]);

    // Pull the token off the canonical URL and reuse it across transformations.
    parse_str((string) parse_url($mediaItem->getUrl(), PHP_URL_QUERY), $query);
    $token = $query['token'];

    // Same item, but a webp conversion via URL.
    get("/media/{$mediaItem->id}/test.webp?token={$token}")
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'image/webp');

    // Same item, but resized to 200px wide.
    get("/media/{$mediaItem->id}/w_200/test.jpg?token={$token}")
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'image/jpeg');

    // Same item, png conversion at 150px wide — single token covers it all.
    get("/media/{$mediaItem->id}/w_150/test.png?token={$token}")
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'image/png');
});

test('a tampered token is rejected', function () {
    $mediaItem = MediaItem::factory()
        ->withContents(disk: 'public', contents: file_get_contents(fixtures('test.jpg')), filename: 'test.jpg')
        ->create([
            'public' => false,
        ]);

    get("/media/{$mediaItem->id}/test.jpg?token=not-a-real-token")
        ->assertForbidden();
});

test('public items still serve without a signature', function () {
    $mediaItem = MediaItem::factory()
        ->withContents(disk: 'public', contents: file_get_contents(fixtures('test.jpg')), filename: 'test.jpg')
        ->create([
            'public' => true,
        ]);

    $url = $mediaItem->getUrl();

    expect($url)->not->toContain('signature=');

    get("/media/{$mediaItem->id}/test.jpg")
        ->assertStatus(200);
});

test('private_by_default config flips new uploads to private', function () {
    config()->set('mediaux.private_by_default', true);

    auth()->login(userFactory()->create());

    postJson('/media', [
        'file' => new UploadedFile(fixtures('test.jpg'), 'test.jpg', 'image/jpeg', null, true),
    ])->assertCreated();

    expect(MediaItem::sole()->public)->toBeFalse();
});

test('the fluent private() flag overrides the config default', function () {
    config()->set('mediaux.private_by_default', false);

    auth()->login(userFactory()->create());

    $request = \Illuminate\Http\Request::create('/media', 'POST', [], [], [
        'file' => new UploadedFile(fixtures('test.jpg'), 'test.jpg', 'image/jpeg', null, true),
    ]);

    \TheJawker\Mediaux\Mediaux::upload($request)->private()->respond();

    expect(MediaItem::sole()->public)->toBeFalse();
});

test('a custom authorize callback can grant access without a signature', function () {
    $owner = userFactory()->create();

    $mediaItem = MediaItem::factory()
        ->withContents(disk: 'public', contents: file_get_contents(fixtures('test.jpg')), filename: 'test.jpg')
        ->create([
            'public' => false,
            'user_id' => $owner->id,
        ]);

    config()->set('mediaux.authorize', function ($request, MediaItem $item) {
        return auth()->check() && auth()->id() === $item->user_id;
    });

    auth()->login($owner);

    get("/media/{$mediaItem->id}/test.jpg")
        ->assertStatus(200);
});

test('a custom authorize callback can deny access even with a valid signature', function () {
    $mediaItem = MediaItem::factory()
        ->withContents(disk: 'public', contents: file_get_contents(fixtures('test.jpg')), filename: 'test.jpg')
        ->create([
            'public' => false,
        ]);

    $url = $mediaItem->getUrl();

    config()->set('mediaux.authorize', fn () => false);

    get($url)
        ->assertForbidden();
});
