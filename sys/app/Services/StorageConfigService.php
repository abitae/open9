<?php

namespace App\Services;

use App\Enums\StorageDriver;
use App\Models\StorageSetting;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use League\Flysystem\Visibility;

class StorageConfigService
{
    public const CACHE_KEY = 'storage_settings';

    public const GCS_DISK = 'gcs_active';

    public function settings(): StorageSetting
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (is_int($cached)) {
            $settings = StorageSetting::query()->find($cached);

            if ($settings instanceof StorageSetting) {
                return $settings;
            }
        } elseif ($cached !== null) {
            Cache::forget(self::CACHE_KEY);
        }

        $settings = StorageSetting::query()->firstOrCreate(
            ['id' => 1],
            ['driver' => StorageDriver::Local],
        );

        Cache::put(self::CACHE_KEY, $settings->getKey(), 300);

        return $settings;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function activeDisk(): string
    {
        $settings = $this->settings();

        if ($settings->driver === StorageDriver::Local) {
            return 'public';
        }

        $this->configureGcsDisk($settings);

        return self::GCS_DISK;
    }

    public function publicUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $settings = $this->settings();

        if ($settings->driver === StorageDriver::Local) {
            if ($settings->local_public_url) {
                return rtrim($settings->local_public_url, '/').'/'.ltrim($path, '/');
            }

            return Storage::disk('public')->url($path);
        }

        if ($settings->gcs_public_url) {
            return rtrim($settings->gcs_public_url, '/').'/'.ltrim($path, '/');
        }

        return Storage::disk($this->activeDisk())->url($path);
    }

    public function configureGcsDisk(StorageSetting $settings): void
    {
        if ($settings->gcs_key_json === null || $settings->gcs_key_json === '') {
            throw new \RuntimeException('Las credenciales de Google Cloud Storage no están configuradas.');
        }

        $keyFile = json_decode(Crypt::decryptString($settings->gcs_key_json), true);

        if (! is_array($keyFile)) {
            throw new \RuntimeException('Las credenciales de Google Cloud Storage son inválidas.');
        }

        config([
            'filesystems.disks.'.self::GCS_DISK => [
                'driver' => 'gcs',
                'project_id' => $settings->gcs_project_id,
                'bucket' => $settings->gcs_bucket,
                'key_file' => $keyFile,
                'visibility' => Visibility::PUBLIC,
            ],
        ]);
    }

    public function testConnection(): bool
    {
        $disk = $this->activeDisk();
        $testPath = 'system/connection-test-'.now()->timestamp.'.txt';
        Storage::disk($disk)->put($testPath, 'open9-connection-test');
        $exists = Storage::disk($disk)->exists($testPath);
        Storage::disk($disk)->delete($testPath);

        return $exists;
    }

    public function registerGcsDriver(): void
    {
        Storage::extend('gcs', function ($app, array $config) {
            $client = new StorageClient([
                'projectId' => $config['project_id'],
                'keyFile' => $config['key_file'],
            ]);

            $bucket = $client->bucket($config['bucket']);
            $adapter = new GoogleCloudStorageAdapter($bucket, $config['path_prefix'] ?? '');

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config,
            );
        });
    }
}
