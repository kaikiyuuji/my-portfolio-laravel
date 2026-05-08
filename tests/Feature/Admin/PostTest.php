<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    private function createPost(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'slug' => 'sample-post',
            'title' => ['pt' => 'Post de teste', 'en' => 'Sample Post'],
            'excerpt' => ['pt' => 'Resumo curto.', 'en' => 'Short summary.'],
            'body' => ['pt' => '<p>Conteúdo</p>', 'en' => '<p>Content</p>'],
            'is_published' => true,
            'published_at' => now()->subDay(),
        ], $overrides));
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => ['pt' => 'Meu Post', 'en' => 'My Post'],
            'excerpt' => ['pt' => 'Resumo', 'en' => 'Summary'],
            'body' => ['pt' => 'Corpo do post.', 'en' => 'Post body.'],
            'is_published' => true,
        ], $overrides);
    }

    // ─── Index ────────────────────────────────────────────────────

    public function test_authenticated_admin_can_view_posts_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/posts');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Posts/Index'));
    }

    public function test_unauthenticated_user_cannot_access_posts(): void
    {
        $this->get('/admin/posts')->assertRedirect('/login');
    }

    // ─── Create ───────────────────────────────────────────────────

    public function test_admin_can_view_post_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/posts/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Posts/Form'));
    }

    // ─── Store ────────────────────────────────────────────────────

    public function test_admin_can_create_a_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/posts', $this->validPayload());

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseCount('posts', 1);
    }

    public function test_post_creation_requires_title_pt(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/posts', $this->validPayload([
            'title' => ['pt' => '', 'en' => 'My Post'],
        ]));

        $response->assertSessionHasErrors('title.pt');
    }

    public function test_post_creation_requires_body_pt(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/posts', $this->validPayload([
            'body' => ['pt' => '', 'en' => 'Body'],
        ]));

        $response->assertSessionHasErrors('body.pt');
    }

    public function test_post_creation_auto_generates_slug_from_title(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/posts', $this->validPayload([
            'title' => ['pt' => 'Como construí meu portfolio', 'en' => 'How I built it'],
        ]));

        $this->assertDatabaseHas('posts', ['slug' => 'como-construi-meu-portfolio']);
    }

    public function test_post_creation_rejects_duplicate_slug(): void
    {
        $user = User::factory()->create();
        $this->createPost(['slug' => 'taken-slug']);

        $response = $this->actingAs($user)->post('/admin/posts', $this->validPayload([
            'slug' => 'taken-slug',
        ]));

        $response->assertSessionHasErrors('slug');
    }

    public function test_published_post_without_published_at_defaults_to_now(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/posts', $this->validPayload([
            'is_published' => true,
            'published_at' => null,
        ]));

        $post = Post::first();
        $this->assertNotNull($post->published_at);
    }

    public function test_unpublished_post_has_null_published_at(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/posts', $this->validPayload([
            'is_published' => false,
            'published_at' => now()->toDateTimeString(),
        ]));

        $post = Post::first();
        $this->assertNull($post->published_at);
    }

    // ─── Image Upload ─────────────────────────────────────────────

    public function test_admin_can_upload_post_cover_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/posts', $this->validPayload([
            'image' => UploadedFile::fake()->image('cover.jpg', 1200, 800),
        ]));

        $response->assertSessionHasNoErrors();
        $post = Post::first();
        $this->assertNotNull($post->image_path);
    }

    public function test_post_image_rejects_non_image_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/posts', $this->validPayload([
            'image' => UploadedFile::fake()->create('doc.pdf', 1024, 'application/pdf'),
        ]));

        $response->assertSessionHasErrors('image');
    }

    // ─── Edit ─────────────────────────────────────────────────────

    public function test_admin_can_view_post_edit_form(): void
    {
        $user = User::factory()->create();
        $post = $this->createPost();

        $response = $this->actingAs($user)->get("/admin/posts/{$post->slug}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Posts/Form')->has('post'));
    }

    public function test_edit_returns_404_for_invalid_slug(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/posts/inexistente/edit')->assertNotFound();
    }

    // ─── Update ───────────────────────────────────────────────────

    public function test_admin_can_update_a_post(): void
    {
        $user = User::factory()->create();
        $post = $this->createPost();

        $response = $this->actingAs($user)->put("/admin/posts/{$post->slug}", $this->validPayload([
            'title' => ['pt' => 'Atualizado', 'en' => 'Updated'],
        ]));

        $response->assertSessionHasNoErrors();
        $post->refresh();
        $this->assertEquals('Atualizado', $post->getTranslation('title', 'pt'));
    }

    public function test_update_allows_keeping_same_slug(): void
    {
        $user = User::factory()->create();
        $post = $this->createPost(['slug' => 'meu-post']);

        $response = $this->actingAs($user)->put("/admin/posts/{$post->slug}", $this->validPayload([
            'slug' => 'meu-post',
        ]));

        $response->assertSessionHasNoErrors();
    }

    // ─── Destroy ──────────────────────────────────────────────────

    public function test_admin_can_delete_a_post(): void
    {
        $user = User::factory()->create();
        $post = $this->createPost();

        $response = $this->actingAs($user)->delete("/admin/posts/{$post->slug}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_deleting_post_removes_image_from_storage(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $path = Storage::disk('public')->put('posts', UploadedFile::fake()->image('test.jpg'));
        $post = $this->createPost(['image_path' => $path]);

        $this->actingAs($user)->delete("/admin/posts/{$post->slug}");

        Storage::disk('public')->assertMissing($path);
    }
}
