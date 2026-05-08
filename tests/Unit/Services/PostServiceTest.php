<?php

namespace Tests\Unit\Services;

use App\Models\Post;
use App\Services\ImageUploadService;
use App\Services\PostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeService(?ImageUploadService $imageService = null): PostService
    {
        return new PostService(
            $imageService ?? Mockery::mock(ImageUploadService::class)
        );
    }

    private function createPost(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'slug' => 'sample',
            'title' => ['pt' => 'Título', 'en' => 'Title'],
            'body' => ['pt' => 'Corpo', 'en' => 'Body'],
            'is_published' => true,
            'published_at' => now()->subDay(),
        ], $overrides));
    }

    // ─── all() ────────────────────────────────────────────────────

    public function test_all_returns_posts_ordered_by_published_at_desc(): void
    {
        $this->createPost(['slug' => 'older', 'published_at' => now()->subDays(5)]);
        $this->createPost(['slug' => 'newer', 'published_at' => now()->subDay()]);

        $service = $this->makeService();
        $result = $service->all();

        $this->assertEquals('newer', $result->first()->slug);
    }

    // ─── published() ──────────────────────────────────────────────

    public function test_published_excludes_unpublished_posts(): void
    {
        $this->createPost(['slug' => 'published', 'is_published' => true]);
        $this->createPost(['slug' => 'draft', 'is_published' => false, 'published_at' => null]);

        $service = $this->makeService();
        $result = $service->published();

        $this->assertEquals(1, $result->total());
        $this->assertEquals('published', $result->items()[0]->slug);
    }

    public function test_published_excludes_future_dated_posts(): void
    {
        $this->createPost(['slug' => 'now', 'published_at' => now()->subHour()]);
        $this->createPost(['slug' => 'future', 'published_at' => now()->addDay()]);

        $service = $this->makeService();
        $result = $service->published();

        $this->assertEquals(1, $result->total());
        $this->assertEquals('now', $result->items()[0]->slug);
    }

    // ─── findPublishedBySlug() ────────────────────────────────────

    public function test_find_published_by_slug_returns_post(): void
    {
        $this->createPost(['slug' => 'live']);

        $service = $this->makeService();
        $post = $service->findPublishedBySlug('live');

        $this->assertNotNull($post);
        $this->assertEquals('live', $post->slug);
    }

    public function test_find_published_by_slug_returns_null_for_draft(): void
    {
        $this->createPost(['slug' => 'draft', 'is_published' => false, 'published_at' => null]);

        $service = $this->makeService();

        $this->assertNull($service->findPublishedBySlug('draft'));
    }

    public function test_find_published_by_slug_returns_null_for_missing(): void
    {
        $service = $this->makeService();

        $this->assertNull($service->findPublishedBySlug('does-not-exist'));
    }

    // ─── store() ──────────────────────────────────────────────────

    public function test_store_generates_slug_from_pt_title_when_empty(): void
    {
        $service = $this->makeService();
        $post = $service->store([
            'title' => ['pt' => 'Olá Mundo Bonito', 'en' => 'Hello'],
            'body' => ['pt' => 'Corpo', 'en' => 'Body'],
            'is_published' => true,
        ]);

        $this->assertEquals('ola-mundo-bonito', $post->slug);
    }

    public function test_store_disambiguates_duplicate_slug_with_suffix(): void
    {
        $this->createPost(['slug' => 'meu-post']);

        $service = $this->makeService();
        $post = $service->store([
            'slug' => 'meu-post',
            'title' => ['pt' => 'Outro', 'en' => 'Another'],
            'body' => ['pt' => 'Body', 'en' => 'Body'],
            'is_published' => true,
        ]);

        $this->assertEquals('meu-post-2', $post->slug);
    }

    public function test_store_published_without_published_at_uses_now(): void
    {
        $service = $this->makeService();
        $post = $service->store([
            'title' => ['pt' => 'Agora', 'en' => 'Now'],
            'body' => ['pt' => 'Body', 'en' => 'Body'],
            'is_published' => true,
            'published_at' => null,
        ]);

        $this->assertNotNull($post->published_at);
    }

    public function test_store_unpublished_forces_null_published_at(): void
    {
        $service = $this->makeService();
        $post = $service->store([
            'title' => ['pt' => 'Rascunho', 'en' => 'Draft'],
            'body' => ['pt' => 'Body', 'en' => 'Body'],
            'is_published' => false,
            'published_at' => now()->toDateTimeString(),
        ]);

        $this->assertNull($post->published_at);
    }

    // ─── update() ─────────────────────────────────────────────────

    public function test_update_modifies_existing_post(): void
    {
        $post = $this->createPost(['slug' => 'antigo']);

        $service = $this->makeService();
        $result = $service->update($post, [
            'title' => ['pt' => 'Novo Título', 'en' => 'New Title'],
            'body' => ['pt' => 'Novo corpo', 'en' => 'New body'],
            'is_published' => true,
        ]);

        $this->assertEquals('Novo Título', $result->getTranslation('title', 'pt'));
    }

    // ─── updateImage() ────────────────────────────────────────────

    public function test_update_image_stores_file_and_updates_path(): void
    {
        Storage::fake('public');

        $post = $this->createPost(['image_path' => null]);

        $mockUpload = Mockery::mock(ImageUploadService::class);
        $mockUpload->shouldReceive('store')->once()->andReturn('posts/new.jpg');
        $mockUpload->shouldReceive('delete')->once()->with(null);

        $service = $this->makeService($mockUpload);
        $path = $service->updateImage($post, UploadedFile::fake()->image('cover.jpg'));

        $this->assertEquals('posts/new.jpg', $path);
    }

    // ─── destroy() ────────────────────────────────────────────────

    public function test_destroy_removes_post_and_deletes_image(): void
    {
        $post = $this->createPost(['image_path' => 'posts/test.jpg']);

        $mockUpload = Mockery::mock(ImageUploadService::class);
        $mockUpload->shouldReceive('delete')->once()->with('posts/test.jpg');

        $service = $this->makeService($mockUpload);
        $service->destroy($post);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
