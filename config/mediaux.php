<?php

use Illuminate\Http\Request;
use TheJawker\Mediaux\Models\MediaItem;
use TheJawker\Mediaux\Support\MediaSignature;

return [
    'user_model' => 'App\\Models\\User',

    'disable_routes' => false,

    /*
     * When true, newly uploaded media is marked private by default. Private
     * items are only served when the configured authorize callback returns
     * true — by default that means the request must carry a valid token query
     * parameter for the requested media item.
     *
     * Existing items are unaffected; this only governs new uploads.
     */
    'private_by_default' => false,

    /*
     * How long tokens minted by the default get_url callback remain valid,
     * in minutes. Only used for private items.
     */
    'signed_url_ttl' => 60,

    /*
     * Authorization callback executed before serving any media item. Return
     * true to allow the request, false to respond with 403.
     *
     * Default behavior:
     *   - public items are always served
     *   - private items require a ?token=... query parameter that was minted
     *     by MediaSignature::mint() for the same MediaItem and is unexpired
     *
     * The token covers (id, expires) and is independent of the request path,
     * so the same token grants access to every transformation URL of the
     * media item (resize, format conversion, etc.). This is essential for
     * mediaux's URL-driven transformations to keep working under private mode.
     *
     * Override this to wire mediaux into your own authorization (Gates,
     * policies, ownership checks, multi-tenant scoping, etc.).
     */
    'authorize' => function (Request $request, MediaItem $mediaItem): bool {
        if ($mediaItem->public) {
            return true;
        }

        $token = $request->query('token');

        if (! is_string($token)) {
            return false;
        }

        return MediaSignature::verify($token, $mediaItem);
    },

    /*
     * URL generator. Public items get a plain route URL; private items get
     * the same route URL with a ?token=... query parameter that the default
     * authorize callback can verify.
     *
     * Override this to mint URLs differently — for example, to return a
     * Storage::temporaryUrl() pointing straight at S3 for private items.
     */
    'get_url' => function (Request $request, MediaItem $mediaItem): string {
        if ($mediaItem->public) {
            return route('media.fetch', [$mediaItem, $mediaItem->original_filename]);
        }

        return route('media.fetch', [
            'mediaItem' => $mediaItem,
            'filename' => $mediaItem->original_filename,
            'token' => MediaSignature::mint($mediaItem, (int) config('mediaux.signed_url_ttl')),
        ]);
    },
];
