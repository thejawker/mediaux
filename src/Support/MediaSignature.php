<?php

namespace TheJawker\Mediaux\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use TheJawker\Mediaux\Models\MediaItem;

/**
 * Mints and verifies tokens that grant access to a specific MediaItem,
 * independent of the request path. Unlike Laravel's URL::temporarySignedRoute(),
 * the same token is valid across every transformation URL of the same item —
 * which is essential for mediaux, since the whole point is that the frontend
 * can swap path segments to resize, reformat, etc.
 *
 * Token format (URL-safe base64 encoded):
 *   "{mediaItem.id}|{expires_unix_ts}|{hmac_sha256(id|expires, app.key)}"
 */
class MediaSignature
{
    public static function mint(MediaItem $mediaItem, int $ttlMinutes): string
    {
        $expires = Carbon::now()->addMinutes($ttlMinutes)->getTimestamp();
        $payload = $mediaItem->id.'|'.$expires;
        $signature = hash_hmac('sha256', $payload, self::key());

        return self::base64UrlEncode($payload.'|'.$signature);
    }

    public static function verify(string $token, MediaItem $mediaItem): bool
    {
        $raw = self::base64UrlDecode($token);

        if ($raw === null) {
            return false;
        }

        $parts = explode('|', $raw);

        if (count($parts) !== 3) {
            return false;
        }

        [$id, $expires, $signature] = $parts;

        if ((int) $id !== (int) $mediaItem->id) {
            return false;
        }

        if ((int) $expires < Carbon::now()->getTimestamp()) {
            return false;
        }

        $expected = hash_hmac('sha256', $id.'|'.$expires, self::key());

        return hash_equals($expected, $signature);
    }

    private static function key(): string
    {
        $key = Config::get('app.key');

        if (is_string($key) && str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return (string) $key;
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $value): ?string
    {
        $padded = str_pad($value, strlen($value) + (4 - strlen($value) % 4) % 4, '=');
        $decoded = base64_decode(strtr($padded, '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }
}
