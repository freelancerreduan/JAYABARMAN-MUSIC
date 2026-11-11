<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * License of this copy of the website, checked against the Smart BD Host license panel.
 *
 * The verified answer is kept (encrypted and split) by Vault and checked on every request, so it
 * cannot be edited by hand. The answer names the domain, so a copy moved to another domain asks for a
 * license again.
 *
 * Live domain: the real license key is required; the panel's signed answer unlocks the site. The panel
 * is only asked again every few hours, after the page has been sent; when it is offline the site keeps
 * the last answer and keeps running. Only a real signed answer can lock or unlock a live site.
 *
 * Developer (local) machine: the panel is never contacted. The site is unlocked only by typing the
 * developer name from config('license.developer_name'); nothing else works, and this never unlocks a
 * live domain.
 */
class License
{
    /** States in which a live site runs. "not_started" counts as active, like on the panel. */
    const RUNNING = ['active', 'not_started'];

    private static ?array $cache = null;

    /** This site's domain, written the way the panel writes it. */
    public static function domain(?string $host = null): string
    {
        $d = Str::lower(trim($host ?? request()->getHost()));
        $d = preg_replace('#^[a-z]+://#', '', $d);
        $d = explode(':', explode('/', $d)[0])[0];

        return preg_replace('/^www\./', '', $d);
    }

    public static function isLocal(): bool
    {
        return in_array(self::domain(), config('license.local_hosts', []), true);
    }

    /** The whole saved state (dev unlock, or a live license), cached per request. */
    private static function state(): ?array
    {
        if (self::$cache !== null) {
            return self::$cache ?: null;
        }
        $state = Vault::get();
        self::$cache = $state ?: [];

        return $state ?: null;
    }

    /** True when the saved state is a developer unlock for this local machine. */
    public static function isDeveloper(): bool
    {
        $state = self::state();

        return $state && ! empty($state['dev']) && self::isLocal() && ($state['d'] ?? '') === self::domain();
    }

    /** The verified panel payload of a live license, or null (never set for a developer unlock). */
    public static function current(): ?array
    {
        $state = self::state();
        if (! $state || ! empty($state['dev'])) {
            return null;
        }
        $data = self::verify($state['p'] ?? '', $state['g'] ?? '');

        if (! $data || ($data['d'] ?? '') !== self::domain()
            || (isset($data['k']) && ! hash_equals($data['k'], hash('sha256', $state['key'] ?? '')))) {
            return null;
        }

        return $data;
    }

    /** Is this copy allowed to run? */
    public static function valid(): bool
    {
        if (self::isDeveloper()) {
            return true;
        }

        return in_array(self::current()['s'] ?? '', self::RUNNING, true);
    }

    /** The saved live key (shown masked on the license page). */
    public static function key(): ?string
    {
        $state = self::state();

        return ($state && empty($state['dev'])) ? ($state['key'] ?? null) : null;
    }

    /**
     * Developer unlock on a local machine. Returns true when the name matched and the site is unlocked.
     * Never unlocks a live domain.
     */
    public static function activateDeveloper(string $name): bool
    {
        if (! self::isLocal()) {
            return false;
        }
        $want = (string) config('license.developer_name');
        if ($want === '' || ! hash_equals($want, trim($name))) {
            return false;
        }
        Vault::put(['dev' => true, 'd' => self::domain(), 'at' => time()]);
        self::$cache = null;

        return true;
    }

    /**
     * Ask the panel about a key for this (live) domain and save a good answer.
     * Returns ['ok' => bool, 'status' => string, 'data' => payload|null]; status "offline" when the panel could not answer.
     */
    public static function activate(string $key): array
    {
        $key = Str::upper(trim($key));
        $answer = self::ask($key);
        if (! $answer) {
            return ['ok' => false, 'status' => 'offline', 'data' => null];
        }
        [$p, $g, $data] = $answer;

        if (($data['s'] ?? '') !== 'domain' && ($data['s'] ?? '') !== 'invalid') {
            self::save(['key' => $key, 'p' => $p, 'g' => $g, 'checked_at' => time(), 'seen' => []]);
        }

        return ['ok' => in_array($data['s'], self::RUNNING, true), 'status' => $data['s'], 'data' => $data];
    }

    /** Re-check with the panel when the last check is old. Called after the response is sent. */
    public static function recheckIfDue(): void
    {
        $state = self::state();
        if (! $state || ! empty($state['dev']) || empty($state['key']) || self::isLocal()) {
            return;
        }
        if (time() - ($state['checked_at'] ?? 0) < config('license.recheck_hours', 12) * 3600) {
            return;
        }
        self::save(['checked_at' => time()] + $state);   // mark first, so a slow panel is not re-asked every page

        $answer = self::ask($state['key']);
        if (! $answer) {
            return;
        }
        [$p, $g, $data] = $answer;
        if (self::newer($data, $state)) {
            self::save(['p' => $p, 'g' => $g, 'checked_at' => time()] + $state);
        }
    }

    /**
     * A state sent by the panel (push): active, expired or suspended.
     * Returns null when accepted, otherwise the reason it was refused.
     */
    public static function acceptPush(string $p, string $g): ?string
    {
        $data = self::verify($p, $g);
        if (! $data) {
            return 'bad signature';
        }
        if (($data['d'] ?? '') !== self::domain()) {
            return 'wrong domain';
        }
        $state = self::state();
        if (! $state || ! empty($state['dev'])) {
            return 'not activated';
        }
        $sent = strtotime($data['t'] ?? '') ?: 0;
        if (abs(time() - $sent) > 3600 || in_array($data['n'] ?? '', $state['seen'] ?? [], true)) {
            return 'old message';
        }
        if (! self::newer($data, $state)) {
            return 'old message';
        }

        $state['seen'] = array_slice(array_merge($state['seen'] ?? [], [$data['n'] ?? '']), -20);
        self::save(['p' => $p, 'g' => $g, 'checked_at' => time()] + $state);

        return null;
    }

    /** Signed payload from the panel, or null when the signature does not match. */
    public static function verify(string $p, string $g): ?array
    {
        $json = base64_decode($p, true);
        $sig = base64_decode($g, true);
        $key = openssl_pkey_get_public(config('license.public_key'));
        if ($json === false || $sig === false || ! $key || openssl_verify($json, $sig, $key, OPENSSL_ALGO_SHA256) !== 1) {
            return null;
        }
        $data = json_decode($json, true);

        return is_array($data) && isset($data['s'], $data['d']) ? $data : null;
    }

    /** [p, g, payload] from the panel, or null when it is offline / answers something unsigned. */
    private static function ask(string $key): ?array
    {
        $nonce = Str::random(32);
        try {
            $res = Http::timeout(config('license.timeout', 5))->connectTimeout(3)->acceptJson()->asForm()
                ->post(config('license.server'), ['k' => $key, 'd' => self::domain(), 'n' => $nonce]);
            if (! $res->successful()) {
                return null;
            }
            $p = (string) $res->json('p');
            $g = (string) $res->json('g');
        } catch (Throwable) {
            return null;
        }
        $data = self::verify($p, $g);

        if (! $data || ($data['n'] ?? '') !== $nonce || ($data['d'] ?? '') !== self::domain()
            || ($data['k'] ?? '') !== hash('sha256', $key)) {
            return null;
        }

        return [$p, $g, $data];
    }

    /** True when $data is not older than the answer already saved. */
    private static function newer(array $data, array $state): bool
    {
        $saved = self::verify($state['p'] ?? '', $state['g'] ?? '');

        return ! $saved || (strtotime($data['t'] ?? '') ?: 0) >= (strtotime($saved['t'] ?? '') ?: 0);
    }

    private static function save(array $state): void
    {
        Vault::put($state);
        self::$cache = null;
    }

    /** For the "use another key" button, and to go back to a clean activation page. */
    public static function forget(): void
    {
        Vault::forget();
        self::$cache = null;
    }
}
