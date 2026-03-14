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
        $appUrl = rtrim($data['app_url'] ?? 'http://localhost', '/');
        $appKey = 'base64:' . base64_encode(random_bytes(32));

        return <<<ENV
APP_NAME="HeadlessCMS"
APP_ENV=production
APP_KEY={$appKey}
APP_DEBUG=false
APP_URL={$appUrl}

LOG_CHANNEL=daily
LOG_LEVEL=error
LOG_DAYS=14

DB_CONNECTION=mysql
DB_HOST={$data['db_host']}
DB_PORT={$data['db_port']}
DB_DATABASE={$data['db_database']}
DB_USERNAME={$data['db_username']}
DB_PASSWORD={$data['db_password']}

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

        file_put_contents($envPath, $content);
        return true;
    }

    public function runMigrations(): bool
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
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
        return User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => 'superadmin',
            'is_active' => true,
        ]);
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
