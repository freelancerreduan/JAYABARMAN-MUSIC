<?php

namespace App\Http\Middleware;

use App\Support\Installer;
use App\Support\License;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * The whole site is closed until it has a license for this domain and a finished database setup.
 *
 * This runs as a GLOBAL middleware, before routing, so there is no license/install route to find:
 * the activation and database pages are drawn in place on whatever URL was opened, and their forms
 * post back to the same URL. A short double-submit cookie token stands in for CSRF (there is no
 * session yet before the database is set up).
 */
class RequireLicense
{
    /** Machine-to-machine endpoints that must never be gated. */
    private const PASS = ['license/push', 'up'];

    public function handle(Request $request, Closure $next): Response
    {
        $path = trim($request->path(), '/');
        if (in_array($path, self::PASS, true)) {
            return $next($request);
        }

        // The two inline forms carry a marker; handle them wherever they were posted from.
        if ($request->isMethod('post') && $request->has('__lic')) {
            return $this->doActivate($request);
        }
        if ($request->isMethod('post') && $request->has('__inst')) {
            return $this->doDatabase($request);
        }

        if (! License::valid()) {
            return $this->showLicense($request);
        }
        if (! Installer::installed()) {
            return $this->showDatabase($request);
        }

        return $next($request);
    }

    /** After the page is sent: ask the panel again when due. A panel that is down changes nothing. */
    public function terminate(Request $request, Response $response): void
    {
        License::recheckIfDue();
    }

    // ---------------------------------------------------------------- license

    private function showLicense(Request $request, ?string $error = null): Response
    {
        $token = bin2hex(random_bytes(16));
        $html = view('install.license', [
            'local' => License::isLocal(),
            'domain' => License::domain(),
            'license' => License::current(),
            'key' => License::key(),
            'vendor' => config('license.vendor'),
            'token' => $token,
            'error' => $error,
        ])->render();

        return $this->page($html, $token);
    }

    private function doActivate(Request $request): Response
    {
        if (! $this->tokenOk($request)) {
            return $this->showLicense($request, 'Your session expired. Please try again.');
        }
        if ($this->tooMany($request)) {
            return $this->showLicense($request, 'Too many attempts. Please wait a minute and try again.');
        }

        // Developer unlock (local machines only)
        if ($request->filled('dev')) {
            if (License::activateDeveloper((string) $request->input('dev'))) {
                return redirect('/');
            }

            return $this->showLicense($request, 'That developer name is not correct.');
        }

        // Real license key (live domains)
        $key = trim((string) $request->input('key'));
        if ($key === '') {
            return $this->showLicense($request, 'Please enter your license key.');
        }
        $result = License::activate($key);
        if (! $result['ok']) {
            return $this->showLicense($request, self::MESSAGES[$result['status']] ?? self::MESSAGES['invalid']);
        }

        return redirect('/');
    }

    private const MESSAGES = [
        'offline' => 'The license server could not be reached. Please check the internet connection and try again in a few minutes.',
        'invalid' => 'This license key was not found. Please check it and try again.',
        'domain' => 'This license key belongs to another website address. Please use the key made for this domain.',
        'expired' => 'This license has expired. Please renew it to continue.',
        'suspended' => 'This license has been suspended. Please contact us to activate it again.',
    ];

    // --------------------------------------------------------------- database

    private function showDatabase(Request $request, ?string $error = null, array $old = []): Response
    {
        $token = bin2hex(random_bytes(16));
        $html = view('install.database', [
            'db' => $old ?: Installer::currentDb(),
            'token' => $token,
            'error' => $error,
        ])->render();

        return $this->page($html, $token);
    }

    private function doDatabase(Request $request): Response
    {
        if (! License::valid()) {
            return $this->showLicense($request);
        }
        if (Installer::installed()) {
            return redirect('/');
        }
        if (! $this->tokenOk($request) || $this->tooMany($request)) {
            return $this->showDatabase($request, 'Your session expired. Please try again.');
        }

        $db = [
            'host' => trim((string) $request->input('host')),
            'port' => (string) ($request->input('port') ?: '3306'),
            'database' => trim((string) $request->input('database')),
            'username' => trim((string) $request->input('username')),
            'password' => (string) $request->input('password', ''),
        ];
        $old = $db;
        unset($old['password']);

        foreach (['host', 'database', 'username'] as $need) {
            if ($db[$need] === '') {
                return $this->showDatabase($request, 'Please fill in the host, database name and database user.', $old);
            }
        }

        if ($error = Installer::testConnection($db)) {
            return $this->showDatabase($request, 'Could not connect to the database: '.$error, $old);
        }

        $adminEmail = trim((string) $request->input('admin_email'));
        $adminPass = (string) $request->input('admin_password');
        if (! Installer::hasTables($db) && ($adminEmail === '' || strlen($adminPass) < 8)) {
            return $this->showDatabase($request, 'This database is empty, so a new admin account will be created. Enter the admin email and a password (8+ characters).', $old);
        }

        try {
            $fresh = Installer::setupDatabase($db, ['email' => $adminEmail, 'password' => $adminPass]);
        } catch (\Throwable $e) {
            return $this->showDatabase($request, 'The database was reached, but setting it up failed: '.$e->getMessage(), $old);
        }

        return redirect('/admin/login');
    }

    // ------------------------------------------------------------------ utils

    private function page(string $html, string $token): Response
    {
        return response($html, 200)->withCookie(
            Cookie::make('lc_t', $token, 0, '/', null, request()->isSecure(), true, false, 'Lax')
        );
    }

    private function tokenOk(Request $request): bool
    {
        $sent = (string) $request->input('_t');
        $seen = (string) $request->cookie('lc_t');

        return $sent !== '' && $seen !== '' && hash_equals($seen, $sent);
    }

    private function tooMany(Request $request): bool
    {
        $k = 'lic:'.$request->ip();
        if (RateLimiter::tooManyAttempts($k, 12)) {
            return true;
        }
        RateLimiter::hit($k, 60);

        return false;
    }
}
