<?php

namespace Tests\Unit\Services;

use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadServiceTest extends TestCase
{
    private function makeService(): ImageUploadService
    {
        return new ImageUploadService;
    }

    // ─── store() ──────────────────────────────────────────────────

    public function test_store_saves_file_to_specified_directory(): void
    {
        Storage::fake('public');

        $service = $this->makeService();
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $path = $service->store($file, 'avatars');

        $this->assertNotEmpty($path);
        $this->assertStringStartsWith('avatars/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_store_generates_unique_filename(): void
    {
        Storage::fake('public');

        $service = $this->makeService();

        $path1 = $service->store(UploadedFile::fake()->image('photo.jpg'), 'test');
        $path2 = $service->store(UploadedFile::fake()->image('photo.jpg'), 'test');

        $this->assertNotEquals($path1, $path2);
    }

    // ─── delete() ─────────────────────────────────────────────────

    public function test_delete_removes_existing_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('to-delete.jpg');
        $path = Storage::disk('public')->put('avatars', $file);

        Storage::disk('public')->assertExists($path);

        $service = $this->makeService();
        $service->delete($path);

        Storage::disk('public')->assertMissing($path);
    }

    public function test_delete_handles_null_path_gracefully(): void
    {
        $service = $this->makeService();

        // Should not throw any exception
        $service->delete(null);

        $this->assertTrue(true);
    }

    public function test_delete_handles_nonexistent_file_gracefully(): void
    {
        Storage::fake('public');

        $service = $this->makeService();

        // Should not throw any exception
        $service->delete('nonexistent/file.jpg');

        $this->assertTrue(true);
    }

    // ─── url() ────────────────────────────────────────────────────

    public function test_url_returns_public_url_for_existing_path(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg');
        $path = Storage::disk('public')->put('avatars', $file);

        $service = $this->makeService();
        $url = $service->url($path);

        $this->assertNotNull($url);
        $this->assertIsString($url);
    }

    public function test_url_returns_null_for_null_path(): void
    {
        $service = $this->makeService();
        $url = $service->url(null);

        $this->assertNull($url);
    }
}
