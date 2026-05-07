<?php

namespace Tests\Unit\Services;

use App\Models\SocialLink;
use App\Services\SocialLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLinkServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeService(): SocialLinkService
    {
        return new SocialLinkService();
    }

    private function createLink(array $overrides = []): SocialLink
    {
        return SocialLink::create(array_merge([
            'platform' => 'GitHub',
            'icon_slug' => 'github',
            'url' => 'https://github.com/kaikihirata',
            'order' => 1,
        ], $overrides));
    }

    // ─── all() ────────────────────────────────────────────────────

    public function test_all_returns_links_ordered_by_order(): void
    {
        $this->createLink(['platform' => 'LinkedIn', 'icon_slug' => 'linkedin', 'url' => 'https://linkedin.com', 'order' => 2]);
        $this->createLink(['platform' => 'GitHub', 'icon_slug' => 'github', 'order' => 1]);

        $service = $this->makeService();
        $result = $service->all();

        $this->assertEquals('GitHub', $result->first()->platform);
        $this->assertEquals('LinkedIn', $result->last()->platform);
    }

    // ─── store() ──────────────────────────────────────────────────

    public function test_store_creates_new_social_link(): void
    {
        $service = $this->makeService();
        $link = $service->store([
            'platform' => 'Twitter',
            'icon_slug' => 'x',
            'url' => 'https://x.com/test',
            'order' => 1,
        ]);

        $this->assertInstanceOf(SocialLink::class, $link);
        $this->assertDatabaseHas('social_links', ['platform' => 'Twitter']);
    }

    // ─── update() ─────────────────────────────────────────────────

    public function test_update_modifies_existing_link(): void
    {
        $link = $this->createLink();

        $service = $this->makeService();
        $result = $service->update($link, [
            'platform' => 'GitLab',
            'icon_slug' => 'gitlab',
            'url' => 'https://gitlab.com/test',
        ]);

        $this->assertEquals('GitLab', $result->platform);
        $this->assertDatabaseHas('social_links', ['id' => $link->id, 'platform' => 'GitLab']);
    }

    // ─── destroy() ────────────────────────────────────────────────

    public function test_destroy_removes_link(): void
    {
        $link = $this->createLink();

        $service = $this->makeService();
        $service->destroy($link);

        $this->assertDatabaseMissing('social_links', ['id' => $link->id]);
    }

    // ─── reorder() ────────────────────────────────────────────────

    public function test_reorder_updates_order_column(): void
    {
        $a = $this->createLink(['platform' => 'A', 'icon_slug' => 'a', 'order' => 1]);
        $b = $this->createLink(['platform' => 'B', 'icon_slug' => 'b', 'url' => 'https://b.com', 'order' => 2]);

        $service = $this->makeService();
        $service->reorder([$b->id, $a->id]);

        $this->assertDatabaseHas('social_links', ['id' => $b->id, 'order' => 1]);
        $this->assertDatabaseHas('social_links', ['id' => $a->id, 'order' => 2]);
    }
}
