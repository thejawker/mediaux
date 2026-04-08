# mediaux

Think Cloudinary, but self-hosted and simple. A Laravel package that handles media uploads and transforms files on the way out — resize images, convert formats, turn a video into a GIF — all through the URL.

Upload once, request in any format:

```
/media/42/video.gif           → video to GIF
/media/42/video.png           → grab a frame as PNG
/media/42/photo.webp          → image to WebP
/media/42/w_300/photo.jpg     → resize to 300px wide
```

Everything is converted on first request and cached from there.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/thejawker/mediaux.svg?style=flat-square)](https://packagist.org/packages/thejawker/mediaux)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/thejawker/mediaux/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/thejawker/mediaux/actions?query=workflow%3Arun-tests+branch%3Amain)

## Features

- Upload media with validation, attach to any model
- On-the-fly transformations via URL — format conversion, resizing, cross-media conversion (video to GIF, video to image)
- Results are cached with ETag support, so you only pay the conversion cost once
- Polymorphic relationships — associate media with any Eloquent model
- Automatic cleanup of temporary/expired media

## Installation

```bash
composer require thejawker/mediaux
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="mediaux-migrations"
php artisan migrate
```

Publish the config:

```bash
php artisan vendor:publish --tag="mediaux-config"
```

## Routes

The package registers these routes under the `/media` prefix:

| Method | URI | Description |
|--------|-----|-------------|
| `POST` | `/media` | Upload a media file |
| `GET` | `/media/{mediaItem}/{filename}` | Fetch a media file |
| `GET` | `/media/{mediaItem}/{options}/{filename}` | Fetch with conversion options |

Routes can be disabled by setting `disable_routes` to `true` in the config if you want to handle routing yourself.

## Usage

### Uploading

```php
// basic upload — returns a 201 with the MediaItem
Mediaux::upload()->respond();

// attach the uploaded media to a model
Mediaux::upload()
    ->associate(function ($mediaItem) {
        $post->mediaItems()->attach($mediaItem);
    })
    ->respond();

// custom validation rules
Mediaux::upload()
    ->customValidation([
        'file' => ['required', 'file', 'image', 'max:5000'],
    ])
    ->respond();
```

### Fetching

```php
// serve from route parameters
Mediaux::fetcher()->respond();

// serve a specific media item
Mediaux::fetcher()
    ->mediaItem($mediaItem)
    ->respond();
```

### URL transformations

The real trick — transformations happen on the way out, driven entirely by the URL. Change the extension to convert formats, add dimension options to resize. Works across media types too.

```
/media/42/photo.webp              → convert to WebP
/media/42/photo.png               → convert to PNG
/media/42/w_300/photo.jpg         → resize to 300px wide
/media/42/h_200/photo.jpg         → resize to 200px tall
/media/42/w_300,h_200/photo.jpg   → resize to 300x200
/media/42/clip.gif                → video to GIF
/media/42/clip.png                → video frame to PNG
```

First request triggers the conversion, everything after that is served from cache with ETag support.

**Supported formats:** JPG, PNG, WebP, GIF for images — MP4, WebM, OGG, GIF for video.

### Attaching media to models

Add the `HasMedia` trait to any Eloquent model:

```php
use TheJawker\Mediaux\Traits\HasMedia;

class Post extends Model
{
    use HasMedia;
}

// then access media through the relationship
$post->mediaItems;
```

### Private media

By default, every uploaded item is public — its URL is unguessable but anyone with the link can fetch it. For apps where media should be locked down (a task attachment that only members of a group should see, a user's private documents, etc.) you can flip mediaux into private mode. Private items are only served when an authorization callback says so. Out of the box, that callback requires a valid `?token=...` query parameter on the request — your API mints these tokens, hands them to clients, and they expire after a configurable TTL.

The token is bound to the **media item's identity**, not the request path, which means the same token works for every URL transformation of the same item — resize, format conversion, frame extraction, all of it. The frontend keeps full freedom to swap path segments at will.

The flow looks like this:

```
1. Client calls your API   →   GET /api/tasks/42
2. Your API authorizes     →   TaskPolicy::view() runs as usual
3. Your API mints a URL    →   $task->image->getUrl() returns a tokenized URL
4. Client uses the URL     →   <img src="https://app.test/media/17/hero.jpg?token=...">
5. mediaux validates       →   default authorize callback: MediaSignature::verify($token, $item)
```

Authorization happens in two places, and they have different jobs:

- **In your API**, you decide whether the requester even gets to see the URL. That's your existing policy/gate logic — mediaux stays out of it.
- **In mediaux**, the `authorize` callback validates the token when the file is actually fetched. The token is what proves the URL is legit; the client doesn't need to be logged in to fetch (which matters for `<img>` tags).

#### Enabling private mode

```php
// config/mediaux.php
return [
    'private_by_default' => true,
    'signed_url_ttl' => 60, // minutes
    // ...
];
```

With `private_by_default => true`, every new upload is marked private. Existing items are unaffected. You can also override per-upload:

```php
Mediaux::upload()->private()->respond();  // force private
Mediaux::upload()->public()->respond();   // force public
```

#### Generating URLs

`getUrl()` automatically does the right thing — public items get a plain route URL, private items get a tokenized URL:

```php
$task->image->getUrl();
// public  → https://app.test/media/17/hero.jpg
// private → https://app.test/media/17/hero.jpg?token=MTd8MTc3NTY5MDAwMHwzZjkx...
```

Drop it into a resource:

```php
class TaskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'title' => $this->title,
            'image' => $this->image?->getUrl(),
        ];
    }
}
```

#### URL transformations still work under private mode

This is the important bit: the token is bound to the **media item**, not the request path. Your frontend keeps the URL it received from the API, peels the `token` query parameter off, and is free to swap path segments to request whatever transformation it wants — the same token validates all of them.

```js
// the URL your API returned
const url = task.image; // "https://app.test/media/17/hero.jpg?token=abc"

// build a 300px-wide webp from it on the client
const u = new URL(url);
const token = u.searchParams.get('token');
const thumb = `${u.origin}/media/17/w_300/hero.webp?token=${token}`;
// → 200 OK, served as image/webp
```

If you'd rather not parse on the client, return the parts separately from your API:

```php
return [
    'id'        => $this->id,
    'image'     => [
        'base'  => url("/media/{$this->image->id}/{$this->image->original_filename}"),
        'token' => parse_url($this->image->getUrl(), PHP_URL_QUERY), // "token=abc"
    ],
];
```

#### Customizing the authorize callback

The default callback handles the common case (public items always pass; private items need a valid `?token=...`). You only need to customize it if you want to allow other access patterns — for example, letting the file's owner fetch it without a token while logged in:

```php
// config/mediaux.php
use TheJawker\Mediaux\Support\MediaSignature;

'authorize' => function (Illuminate\Http\Request $request, TheJawker\Mediaux\Models\MediaItem $mediaItem): bool {
    if ($mediaItem->public) {
        return true;
    }

    $token = $request->query('token');
    if (is_string($token) && MediaSignature::verify($token, $mediaItem)) {
        return true;
    }

    // owner can always fetch their own media when logged in
    return $request->user()?->id === $mediaItem->user_id;
},
```

The callback receives the current request and the resolved `MediaItem` and returns a boolean. Returning `false` aborts with a 403.

#### Customizing URL generation

If you want private files to live on S3 and be served directly via presigned URLs (bypassing your app entirely), override `get_url`:

```php
// config/mediaux.php
'get_url' => function (Illuminate\Http\Request $request, TheJawker\Mediaux\Models\MediaItem $mediaItem): string {
    if ($mediaItem->public) {
        return route('media.fetch', [$mediaItem, $mediaItem->original_filename]);
    }

    return Storage::disk($mediaItem->disk)->temporaryUrl(
        $mediaItem->filename,
        now()->addMinutes(config('mediaux.signed_url_ttl')),
    );
},
```

#### Caveats

- **URL-level privacy, not filesystem-level.** With the default setup, private files still live on whatever disk you configured. The token blocks the route, not the disk. If you point your disk at a public S3 bucket, anyone who knows the filename can fetch directly. To get filesystem-level privacy, use a private disk and override `get_url` (see above).
- **The token is bearer-style.** Anyone holding the token can fetch the file (across any transformation) until it expires. Keep the TTL short for sensitive content, and don't put tokens in URLs that get logged or shared.

### Working with MediaItem

```php
$mediaItem->getUrl();              // public URL
$mediaItem->getType();             // 'image', 'video', or 'other'
$mediaItem->getMimeType();         // e.g. 'image/jpeg'
$mediaItem->getSize();             // file size in bytes
$mediaItem->getHash();             // perceptual hash

$mediaItem->markAsInUse();         // clears expiration (keeps it around)
$mediaItem->deleteWithDependencies(); // removes file + conversions
```

## Requirements

- PHP 8.2+
- Laravel 11, 12, or 13

## Development

```bash
composer test        # run tests
composer analyse     # static analysis
composer format      # code formatting
```

## License

MIT. See [LICENSE.md](LICENSE.md).
