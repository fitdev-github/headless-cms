<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use PDO;
use PDOException;

class SetupService
{
    public function checkRequirements(): array
    {
        return [
            ['name' => 'PHP >= 8.0',                'ok' => version_compare(PHP_VERSION, '8.0.0', '>='), 'required' => true],
            ['name' => 'PDO MySQL extension',       'ok' => extension_loaded('pdo_mysql'),               'required' => true],
            ['name' => 'OpenSSL extension',         'ok' => extension_loaded('openssl'),                 'required' => true],
            ['name' => 'Mbstring extension',        'ok' => extension_loaded('mbstring'),                'required' => true],
            ['name' => 'Fileinfo extension',        'ok' => extension_loaded('fileinfo'),                'required' => true],
            ['name' => 'storage/ writable',         'ok' => is_writable(storage_path()),                'required' => true],
            ['name' => 'bootstrap/cache/ writable', 'ok' => is_writable(base_path('bootstrap/cache')),  'required' => true],
            ['name' => '.env writable',             'ok' => $this->isEnvWritable(),                      'required' => false],
        ];
    }

    public function allRequiredPass(array $requirements): bool
    {
        foreach ($requirements as $req) {
            if ($req['required'] && !$req['ok']) return false;
        }
        return true;
    }

    public function isEnvWritable(): bool
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return is_writable(base_path());
        }
        return is_writable($envPath);
    }

    public function testConnection(array $db): array
    {
        try {
            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']};charset=utf8mb4";
            new PDO($dsn, $db['username'], $db['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            return ['ok' => true, 'message' => 'Connection successful'];
        } catch (PDOException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function generateEnvContent(array $data): string
    {
        $appUrl  = rtrim($data['app_url'] ?? 'http://localhost', '/');

        // ── FIX 1: Read APP_KEY directly from the .env file on disk ─────────────
        // Do NOT use config('app.key') here — it may be stale or empty, which
        // would cause a brand-new random key to be written, invalidating the
        // browser's session cookie and redirecting the user back to step 1.
        $appKey     = '';
        $envPath    = base_path('.env');
        $envOnDisk  = file_exists($envPath) ? file_get_contents($envPath) : '';

        if ($envOnDisk && preg_match('/^APP_KEY=(.+)$/m', $envOnDisk, $m)) {
            $appKey = trim($m[1]);
        }
        if (empty($appKey)) {
            // Fresh install: generate once and keep it
            $appKey = 'base64:' . base64_encode(random_bytes(32));
        }

        // ── FIX 2: Preserve APP_DEBUG so local dev keeps debug=true ─────────────
        $appDebug = 'false';
        if ($envOnDisk && preg_match('/^APP_DEBUG=(.+)$/m', $envOnDisk, $d)) {
            $appDebug = (strtolower(trim($d[1])) === 'true') ? 'true' : 'false';
        }

        $dbHost = $data['db_host']     ?? '127.0.0.1';
        $dbPort = $data['db_port']     ?? '3306';
        $dbName = $data['db_database'] ?? '';
        $dbUser = $data['db_username'] ?? '';
        $dbPass = $data['db_password'] ?? '';

        return <<<ENV
APP_NAME="HeadlessCMS"
APP_ENV=production
APP_KEY={$appKey}
APP_DEBUG={$appDebug}
APP_URL={$appUrl}

LOG_CHANNEL=daily
LOG_LEVEL=error
LOG_DAYS=14

DB_CONNECTION=mysql
DB_HOST={$dbHost}
DB_PORT={$dbPort}
DB_DATABASE={$dbName}
DB_USERNAME={$dbUser}
DB_PASSWORD={$dbPass}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=480

MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=25
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

FILESYSTEM_DISK=public
ENV;
    }

    public function writeEnv(array $data): bool
    {
        $envPath = base_path('.env');
        $content = $this->generateEnvContent($data);

        if (!is_writable(file_exists($envPath) ? $envPath : base_path())) {
            return false;
        }

        $result = file_put_contents($envPath, $content);
        return $result !== false;
    }

    public function runMigrations(): bool
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            return true;
        } catch (\Throwable $e) {
            // "Table already exists" (SQLSTATE 42S01) means a previous wizard
            // run created the tables but the migration records were lost.
            // Stamp all un-recorded migrations as complete so the app works.
            if (str_contains($e->getMessage(), 'already exists')
                || str_contains($e->getMessage(), '42S01')) {
                return $this->stampMissingMigrations();
            }
            return false;
        }
    }

    /**
     * Insert migration records for every .php file under database/migrations
     * that is not already recorded in the migrations table.
     * Used to recover from "table already exists" situations.
     */
    private function stampMissingMigrations(): bool
    {
        try {
            $recorded = \Illuminate\Support\Facades\DB::table('migrations')
                ->pluck('migration')
                ->toArray();

            $batch = (int) \Illuminate\Support\Facades\DB::table('migrations')
                ->max('batch') + 1;

            foreach (glob(database_path('migrations/*.php')) as $file) {
                $name = pathinfo($file, PATHINFO_FILENAME);
                if (!in_array($name, $recorded)) {
                    \Illuminate\Support\Facades\DB::table('migrations')
                        ->insert(['migration' => $name, 'batch' => $batch]);
                }
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function generateAppKey(): void
    {
        Artisan::call('key:generate', ['--force' => true]);
    }

    public function createAdmin(array $data): User
    {
        // ── FIX 3: firstOrCreate with password in the create-attributes ──────────
        // Using User::create() crashes with a duplicate-key error when the wizard
        // is re-run on an existing database.
        // Password MUST be in the create-attributes because the column has no
        // DEFAULT value — omitting it causes "Field 'password' doesn't have a
        // default value" on INSERT.
        $hashed = Hash::make($data['password']);

        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name'      => $data['name'],
                'password'  => $hashed,
                'role'      => 'superadmin',
                'is_active' => true,
            ]
        );

        // If the record already existed (re-run), update name + password too.
        if (!$user->wasRecentlyCreated) {
            $user->name     = $data['name'];
            $user->password = $hashed;
            $user->save();
        }

        return $user;
    }

    public function saveSettings(array $data): void
    {
        Setting::setMany([
            'site_name'    => $data['site_name']    ?? 'HeadlessCMS',
            'app_url'      => rtrim($data['app_url'] ?? config('app.url'), '/'),
            'timezone'     => $data['timezone']     ?? 'UTC',
            'cors_origins' => $data['cors_origins'] ?? '*',
        ]);
    }

    public function markInstalled(): void
    {
        touch(storage_path('app/installed.lock'));
    }

    public function isInstalled(): bool
    {
        return file_exists(storage_path('app/installed.lock'));
    }
}
