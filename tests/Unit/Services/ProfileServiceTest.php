<?php

namespace Tests\Unit\Services;

use App\Models\Profile;
use App\Services\ImageUploadService;
use App\Services\ProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeService(?ImageUploadService $imageService = null): ProfileService
    {
        return new ProfileService(
            $imageService ?? Mockery::mock(ImageUploadService::class)
        );
    }

    // ─── get() ────────────────────────────────────────────────────

    public function test_get_returns_existing_profile(): void
    {
        $profile = Profile::create([
            'name' => 'Kaiki',
            'headline' => 'Developer',
            'bio' => 'Bio text.',
            'email' => 'kaiki@example.com',
        ]);

        $service = $this->makeService();
        $result = $service->get();

        $this->assertEquals($profile->id, $result->id);
        $this->assertEquals('Kaiki', $result->name);
    }

    public function test_get_creates_profile_if_none_exists(): void
    {
        $this->assertDatabaseCount('profiles', 0);

        $service = $this->makeService();
        $result = $service->get();

        $this->assertInstanceOf(Profile::class, $result);
        $this->assertDatabaseCount('profiles', 1);
    }

    // ─── update() ─────────────────────────────────────────────────

    public function test_update_changes_profile_fields(): void
    {
        Profile::create([
            'name' => 'Old Name',
            'headline' => 'Old Headline',
            'bio' => 'Old bio.',
            'email' => 'old@example.com',
        ]);

        $service = $this->makeService();
        $result = $service->update([
            'name' => 'New Name',
            'headline' => 'New Headline',
            'bio' => 'New bio.',
            'email' => 'new@example.com',
        ]);

        $this->assertEquals('New Name', $result->name);
        $this->assertEquals('New Headline', $result->headline);
        $this->assertDatabaseHas('profiles', ['name' => 'New Name']);
    }

    public function test_update_preserves_fields_not_in_data(): void
    {
        Profile::create([
            'name' => 'Kaiki',
            'headline' => 'Developer',
            'bio' => 'Original bio.',
            'email' => 'kaiki@example.com',
            'resume_url' => 'https://example.com/resume.pdf',
        ]);

        $service = $this->makeService();
        $result = $service->update([
            'name' => 'Kaiki Updated',
            'headline' => 'Developer',
            'bio' => 'Original bio.',
            'email' => 'kaiki@example.com',
        ]);

        $this->assertEquals('Kaiki Updated', $result->name);
        $this->assertEquals('https://example.com/resume.pdf', $result->resume_url);
    }

    // ─── updateAvatar() ───────────────────────────────────────────

    public function test_update_avatar_stores_file_and_updates_path(): void
    {
        Storage::fake('public');

        $profile = Profile::create([
            'name' => 'Kaiki',
            'headline' => 'Dev',
            'bio' => 'Bio.',
            'email' => 'kaiki@example.com',
            'avatar_path' => null,
        ]);

        $mockUploadService = Mockery::mock(ImageUploadService::class);
        $mockUploadService->shouldReceive('store')
            ->once()
            ->andReturn('avatars/new-avatar.jpg');
        $mockUploadService->shouldReceive('delete')
            ->once()
            ->with(null);

        $service = $this->makeService($mockUploadService);
        $file = UploadedFile::fake()->image('avatar.jpg', 400, 400);
        $path = $service->updateAvatar($file);

        $this->assertEquals('avatars/new-avatar.jpg', $path);
    }

    public function test_update_avatar_deletes_old_avatar(): void
    {
        Storage::fake('public');

        Profile::create([
            'name' => 'Kaiki',
            'headline' => 'Dev',
            'bio' => 'Bio.',
            'email' => 'kaiki@example.com',
            'avatar_path' => 'avatars/old-avatar.jpg',
        ]);

        $mockUploadService = Mockery::mock(ImageUploadService::class);
        $mockUploadService->shouldReceive('delete')
            ->once()
            ->with('avatars/old-avatar.jpg');
        $mockUploadService->shouldReceive('store')
            ->once()
            ->andReturn('avatars/new-avatar.jpg');

        $service = $this->makeService($mockUploadService);
        $file = UploadedFile::fake()->image('new-avatar.jpg');
        $service->updateAvatar($file);

        $this->assertDatabaseHas('profiles', [
            'avatar_path' => 'avatars/new-avatar.jpg',
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
