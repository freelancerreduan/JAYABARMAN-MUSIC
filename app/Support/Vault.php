<?php

namespace App\Support;

use Throwable;

/**
 * A tiny hidden store for the license state.
 *
 * The state (a small array) is JSON-encoded, encrypted with AES-256-GCM, then the ciphertext is
 * cut into several pieces that are written to unrelated-looking files in different folders. None of
 * the files is a readable JSON on disk, no single file is enough on its own, and every piece carries
 * a checksum of the whole — so a piece that is edited, missing or copied without the others makes the
 * whole thing read back as "nothing saved" and the site asks for a license again.
 *
 * The folders used all survive `php artisan optimize:clear`, `cache:clear`, `config:clear` and
 * `route:clear`, so a normal deploy or cache clear never locks a licensed site out by accident.
 *
 * There is deliberately no admin screen and no route that points at any of this.
 */
class Vault
{
    /** Where the pieces live, relative to the project root. Order matters (it is the join order). */
    private static function pieces(): array
    {
        $base = base_path();

        return [
            $base.'/storage/app/.sysconf/core.dat',
            $base.'/storage/framework/.license/ns.dat',
            $base.'/bootstrap/cache/.sysmeta',
        ];
    }

    /** 32-byte key from the app key and the per-project pepper. */
    private static function key(): string
    {
        return hash('sha256', (string) config('app.key').'|'.(string) config('license.pepper'), true);
    }

    /** Save the state, or wipe it when $state is null. */
    public static function put(?array $state): void
    {
        if ($state === null) {
            self::forget();

            return;
        }

        $plain = json_encode($state, JSON_UNESCAPED_SLASHES);
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        $blob = $iv.$tag.$cipher;                 // 12 + 16 + N bytes
        $stamp = substr(hash('sha256', $blob, true), 0, 8);

        $pieces = self::pieces();
        $n = count($pieces);
        $chunks = str_split($blob, (int) ceil(strlen($blob) / $n)) ?: [''];
        // Always have exactly $n chunks (pad with empty slices for a very short blob)
        $chunks = array_pad($chunks, $n, '');

        foreach ($pieces as $i => $path) {
            // index(1) + total(1) + stamp(8) + chunk  -> base64, no extension hints
            $body = chr($i).chr($n).$stamp.($chunks[$i] ?? '');
            self::write($path, base64_encode($body));
        }
    }

    /** The saved state, or null when nothing valid is stored. */
    public static function get(): ?array
    {
        try {
            $slices = [];
            $stamp = null;
            $total = null;

            foreach (self::pieces() as $i => $path) {
                if (! is_file($path)) {
                    return null;
                }
                $body = base64_decode((string) @file_get_contents($path), true);
                if ($body === false || strlen($body) < 10) {
                    return null;
                }
                $idx = ord($body[0]);
                $n = ord($body[1]);
                $st = substr($body, 2, 8);
                $chunk = substr($body, 10);

                if ($idx !== $i) {
                    return null;
                }
                $total ??= $n;
                $stamp ??= $st;
                if ($n !== $total || ! hash_equals($stamp, $st)) {
                    return null;       // pieces from different saves / tampered
                }
                $slices[$idx] = $chunk;
            }

            if (count($slices) !== $total) {
                return null;
            }
            ksort($slices);
            $blob = implode('', $slices);
            if (! hash_equals($stamp, substr(hash('sha256', $blob, true), 0, 8))) {
                return null;           // the whole thing was altered
            }

            $iv = substr($blob, 0, 12);
            $tag = substr($blob, 12, 16);
            $cipher = substr($blob, 28);
            $plain = openssl_decrypt($cipher, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
            if ($plain === false) {
                return null;
            }
            $data = json_decode($plain, true);

            return is_array($data) ? $data : null;
        } catch (Throwable) {
            return null;
        }
    }

    public static function forget(): void
    {
        foreach (self::pieces() as $path) {
            @unlink($path);
        }
    }

    private static function write(string $path, string $contents): void
    {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        file_put_contents($path, $contents, LOCK_EX);
    }
}
