<?php

namespace Tests\Feature\Public;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private function createPost(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'slug' => 'sample',
            'title' => ['pt' => 'Título', 'en' => 'Title'],
            'body' => ['pt' => 'Corpo do post.', 'en' => 'Post body.'],
            'is_published' => true,
            'published_at' => now()->subDay(),
        ], $overrides));
    }

    // ─── Index ────────────────────────────────────────────────────

    public function test_blog_index_loads_successfully(): void
    {
        $response = $this->get('/blog');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Public/Blog/Index'));
    }

    public function test_blog_index_lists_only_published_posts(): void
    {
        $this->createPost(['slug' => 'live', 'is_published' => true]);
        $this->createPost(['slug' => 'draft', 'is_published' => false, 'published_at' => null]);

        $response = $this->get('/blog');

        $response->assertInertia(fn ($page) => $page
            ->has('posts.data', 1)
            ->where('posts.data.0.slug', 'live')
        );
    }

    public function test_blog_index_excludes_future_dated_posts(): void
    {
        $this->createPost(['slug' => 'now', 'published_at' => now()->subHour()]);
        $this->createPost(['slug' => 'future', 'published_at' => now()->addDay()]);

        $response = $this->get('/blog');

        $response->assertInertia(fn ($page) => $page
            ->has('posts.data', 1)
            ->where('posts.data.0.slug', 'now')
        );
    }

    public function test_blog_index_works_with_no_posts(): void
    {
        $this->get('/blog')->assertOk();
    }

    public function test_blog_index_is_public(): void
    {
        $this->createPost();

        $this->get('/blog')->assertOk();
    }

    // ─── Show ─────────────────────────────────────────────────────

    public function test_blog_show_returns_published_post(): void
    {
        $post = $this->createPost(['slug' => 'visible']);

        $response = $this->get("/blog/{$post->slug}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Public/Blog/Show')
            ->where('post.slug', 'visible')
        );
    }

    public function test_blog_show_returns_404_for_unpublished_post(): void
    {
        $post = $this->createPost([
            'slug' => 'rascunho',
            'is_published' => false,
            'published_at' => null,
        ]);

        $this->get("/blog/{$post->slug}")->assertNotFound();
    }

    public function test_blog_show_returns_404_for_future_dated_post(): void
    {
        $post = $this->createPost([
            'slug' => 'agendado',
            'published_at' => now()->addDay(),
        ]);

        $this->get("/blog/{$post->slug}")->assertNotFound();
    }

    public function test_blog_show_returns_404_for_missing_slug(): void
    {
        $this->get('/blog/does-not-exist')->assertNotFound();
    }

    public function test_blog_show_includes_related_posts(): void
    {
        $main = $this->createPost(['slug' => 'principal']);
        $this->createPost(['slug' => 'rel-1']);
        $this->createPost(['slug' => 'rel-2']);

        $response = $this->get("/blog/{$main->slug}");

        $response->assertInertia(fn ($page) => $page->has('related', 2));
    }

    public function test_blog_show_excludes_self_from_related(): void
    {
        $main = $this->createPost(['slug' => 'unico']);

        $response = $this->get("/blog/{$main->slug}");

        $response->assertInertia(fn ($page) => $page->has('related', 0));
    }
}
