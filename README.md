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
- Laravel 10, 11, or 12

## Development

```bash
composer test        # run tests
composer analyse     # static analysis
composer format      # code formatting
```

## License

MIT. See [LICENSE.md](LICENSE.md).
