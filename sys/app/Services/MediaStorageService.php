<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaStorageService
{
    /**
     * @var list<string>
     */
    private const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'qt'];

    public function __construct(
        private readonly StorageConfigService $storageConfig,
    ) {}

    public function store(TemporaryUploadedFile $file, string $path, ?string $disk = null): string
    {
        $diskName = $disk ?? $this->storageConfig->activeDisk();
        $storedPath = $file->store($path, $diskName);

        if ($storedPath === false) {
            throw new \RuntimeException('No se pudo guardar el archivo subido.');
        }

        return $storedPath;
    }

    public function url(?string $path, ?string $disk = null): ?string
    {
        if ($path === null) {
            return null;
        }

        $path = trim($path);

        if ($path === '') {
            return null;
        }

        if ($this->isPublicUrl($path)) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return url($path);
        }

        if ($disk !== null) {
            /** @var FilesystemAdapter $filesystem */
            $filesystem = Storage::disk($disk);

            return $filesystem->url($path);
        }

        return $this->storageConfig->publicUrl($path);
    }

    public function isVideoPath(string $path): bool
    {
        if ($this->isPublicUrl($path)) {
            $path = parse_url($path, PHP_URL_PATH) ?: $path;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, self::VIDEO_EXTENSIONS, true);
    }

    public function activeDisk(): string
    {
        return $this->storageConfig->activeDisk();
    }

    private function isPublicUrl(string $path): bool
    {
        return str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || str_starts_with($path, '//');
    }
}
