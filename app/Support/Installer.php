<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;

/**
 * First-run setup of a copy of the website: database connection, tables and an "installed" marker.
 * The marker names the domain, so a copy moved elsewhere goes through the setup again.
 */
class Installer
{
    private static function markerPath(): string
    {
        return base_path('storage/app/.sysconf/boot.dat');
    }

    public static function installed(): bool
    {
        $file = self::markerPath();
        if (! is_file($file)) {
            return false;
        }
        $data = json_decode((string) base64_decode((string) @file_get_contents($file), true), true);

        return is_array($data) && ($data['domain'] ?? null) === License::domain();
    }

    public static function markInstalled(): void
    {
        $dir = dirname(self::markerPath());
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        file_put_contents(self::markerPath(), base64_encode(json_encode(['domain' => License::domain(), 'at' => date('c')])));
    }

    /**
     * Runs before anything else (AppServiceProvider::register). Until the setup is done the database
     * may not exist yet, so sessions and cache use files; a missing APP_KEY is created.
     */
    public static function prepareRuntime(): void
    {
        if (app()->runningInConsole()) {
            return;
        }
        if (! config('app.key')) {
            $key = 'base64:'.base64_encode(random_bytes(32));
            self::writeEnv(['APP_KEY' => $key]);
            config(['app.key' => $key]);
        }
        if (! self::installed()) {
            config(['session.driver' => 'file', 'cache.default' => 'file']);
        }
    }

    /** Error message, or null when the details work. */
    public static function testConnection(array $db): ?string
    {
        try {
            new PDO("mysql:host={$db['host']};port={$db['port']};dbname={$db['database']}", $db['username'], $db['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            return null;
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    /** True when the website's tables are already in the database. */
    public static function hasTables(array $db): bool
    {
        $pdo = new PDO("mysql:host={$db['host']};port={$db['port']};dbname={$db['database']}", $db['username'], $db['password']);

        return (bool) $pdo->query("SHOW TABLES LIKE 'settings'")->fetchColumn();
    }

    /**
     * Saves the details, then creates the tables only when the database is empty.
     * Returns true when the tables were created now (fresh database), false when they were already there.
     */
    public static function setupDatabase(array $db, array $admin): bool
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $db['host'],
            'database.connections.mysql.port' => $db['port'],
            'database.connections.mysql.database' => $db['database'],
            'database.connections.mysql.username' => $db['username'],
            'database.connections.mysql.password' => $db['password'],
        ]);
        DB::purge('mysql');

        $fresh = ! (Schema::hasTable('migrations') && Schema::hasTable('settings'));

        Artisan::call('migrate', ['--force' => true]);

        if ($fresh && ! empty($admin['email']) && ! empty($admin['password'])) {
            // The seeder creates this admin when these are set
            putenv('ADMIN_EMAIL='.$admin['email']);
            putenv('ADMIN_PASSWORD='.$admin['password']);
            $_ENV['ADMIN_EMAIL'] = $_SERVER['ADMIN_EMAIL'] = $admin['email'];
            $_ENV['ADMIN_PASSWORD'] = $_SERVER['ADMIN_PASSWORD'] = $admin['password'];
        }
        if ($fresh) {
            Artisan::call('db:seed', ['--force' => true]);
        }

        self::markInstalled();

        self::writeEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $db['host'],
            'DB_PORT' => $db['port'],
            'DB_DATABASE' => $db['database'],
            'DB_USERNAME' => $db['username'],
            'DB_PASSWORD' => $db['password'],
            'APP_URL' => request()->getSchemeAndHttpHost(),
        ]);

        return $fresh;
    }

    /** Current values for the form. */
    public static function currentDb(): array
    {
        $c = config('database.connections.mysql');

        return [
            'host' => $c['host'] ?? '127.0.0.1',
            'port' => (string) ($c['port'] ?? '3306'),
            'database' => $c['database'] ?? '',
            'username' => $c['username'] ?? '',
        ];
    }

    /** Sets KEY=value lines in .env (created from .env.example when missing). */
    public static function writeEnv(array $values): void
    {
        $file = base_path('.env');
        if (! is_file($file) && is_file(base_path('.env.example'))) {
            copy(base_path('.env.example'), $file);
        }
        $env = (string) @file_get_contents($file);

        foreach ($values as $key => $value) {
            $value = (string) $value;
            if ($value !== '' && preg_match('/[\s#"\'\\\\$=]/', $value)) {
                $value = '"'.str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value).'"';
            }
            $line = $key.'='.$value;
            $env = preg_match("/^{$key}=.*$/m", $env)
                ? preg_replace("/^{$key}=.*$/m", str_replace(['\\', '$'], ['\\\\', '\\$'], $line), $env)
                : rtrim($env)."\n".$line."\n";
        }

        file_put_contents($file, $env, LOCK_EX);
    }
}
