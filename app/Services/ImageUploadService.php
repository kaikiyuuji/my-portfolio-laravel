<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageUploadService
{
    /**
     * Store an uploaded file in the specified directory.
     * Uses the default filesystem disk (driven by FILESYSTEM_DISK env var).
     * In production (Laravel Cloud): 's3' (Cloudflare R2 bucket).
     * In local development: 'public' (local storage).
     */
    public function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, config('filesystems.default'));
    }

    /**
     * Delete a file from the default disk.
     * Handles null paths and nonexistent files gracefully.
     */
    public function delete(?string $path): void
    {
        if ($path && Storage::exists($path)) {
            Storage::delete($path);
        }
    }

    /**
     * Get the public URL for a stored file path.
     * Returns null if no path is provided.
     */
    public function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::url($path);
    }
}
