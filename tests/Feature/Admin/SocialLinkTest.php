<?php

namespace Tests\Feature\Admin;

use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLinkTest extends TestCase
{
    use RefreshDatabase;

    private function createSocialLink(array $overrides = []): SocialLink
    {
        return SocialLink::create(array_merge([
            'platform' => 'GitHub',
            'icon_slug' => 'github',
            'url' => 'https://github.com/kaikihirata',
            'order' => 1,
        ], $overrides));
    }

    // ─── Index ────────────────────────────────────────────────────

    public function test_authenticated_admin_can_view_social_links_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/social-links');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/SocialLinks/Index'));
    }

    public function test_unauthenticated_user_cannot_access_social_links(): void
    {
        $this->get('/admin/social-links')->assertRedirect('/login');
    }

    // ─── Create ───────────────────────────────────────────────────

    public function test_admin_can_view_social_link_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/social-links/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/SocialLinks/Form'));
    }

    // ─── Store ────────────────────────────────────────────────────

    public function test_admin_can_create_a_social_link(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/social-links', [
            'platform' => 'LinkedIn',
            'icon_slug' => 'linkedin',
            'url' => 'https://linkedin.com/in/kaikihirata',
            'order' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('social_links', ['platform' => 'LinkedIn']);
    }

    public function test_social_link_requires_platform(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/social-links', [
            'platform' => '',
            'icon_slug' => 'linkedin',
            'url' => 'https://linkedin.com/in/test',
        ]);

        $response->assertSessionHasErrors('platform');
    }

    public function test_social_link_requires_icon_slug(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/social-links', [
            'platform' => 'LinkedIn',
            'icon_slug' => '',
            'url' => 'https://linkedin.com/in/test',
        ]);

        $response->assertSessionHasErrors('icon_slug');
    }

    public function test_social_link_requires_valid_url(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/social-links', [
            'platform' => 'LinkedIn',
            'icon_slug' => 'linkedin',
            'url' => 'not-a-valid-url',
        ]);

        $response->assertSessionHasErrors('url');
    }

    public function test_social_link_requires_url(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/social-links', [
            'platform' => 'LinkedIn',
            'icon_slug' => 'linkedin',
            'url' => '',
        ]);

        $response->assertSessionHasErrors('url');
    }

    // ─── Edit ─────────────────────────────────────────────────────

    public function test_admin_can_view_social_link_edit_form(): void
    {
        $user = User::factory()->create();
        $link = $this->createSocialLink();

        $response = $this->actingAs($user)->get("/admin/social-links/{$link->id}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/SocialLinks/Form')->has('socialLink'));
    }

    // ─── Update ───────────────────────────────────────────────────

    public function test_admin_can_update_a_social_link(): void
    {
        $user = User::factory()->create();
        $link = $this->createSocialLink();

        $response = $this->actingAs($user)->put("/admin/social-links/{$link->id}", [
            'platform' => 'Twitter',
            'icon_slug' => 'x',
            'url' => 'https://x.com/kaikihirata',
            'order' => 2,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('social_links', ['id' => $link->id, 'platform' => 'Twitter']);
    }

    // ─── Destroy ──────────────────────────────────────────────────

    public function test_admin_can_delete_a_social_link(): void
    {
        $user = User::factory()->create();
        $link = $this->createSocialLink();

        $response = $this->actingAs($user)->delete("/admin/social-links/{$link->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('social_links', ['id' => $link->id]);
    }

    public function test_deleting_nonexistent_social_link_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete('/admin/social-links/9999')->assertNotFound();
    }

    // ─── Reorder ──────────────────────────────────────────────────

    public function test_admin_can_reorder_social_links(): void
    {
        $user = User::factory()->create();
        $a = $this->createSocialLink(['platform' => 'GitHub', 'icon_slug' => 'github', 'order' => 1]);
        $b = $this->createSocialLink(['platform' => 'LinkedIn', 'icon_slug' => 'linkedin', 'url' => 'https://linkedin.com/in/test', 'order' => 2]);

        $response = $this->actingAs($user)->put('/admin/social-links/reorder', [
            'ordered_ids' => [$b->id, $a->id],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('social_links', ['id' => $a->id, 'order' => 2]);
        $this->assertDatabaseHas('social_links', ['id' => $b->id, 'order' => 1]);
    }
}
