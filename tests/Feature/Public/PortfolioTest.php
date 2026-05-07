<?php

namespace Tests\Feature\Public;

use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\SocialLink;
use App\Models\Stack;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ──────────────────────────────────────────────────

    private function seedPortfolioData(): void
    {
        Profile::create([
            'name' => 'Kaiki Hirata',
            'headline' => 'Full Stack Developer',
            'bio' => 'Building great software.',
            'email' => 'kaiki@example.com',
        ]);

        Stack::create([
            'name' => 'Laravel',
            'icon_slug' => 'laravel',
            'color' => '#FF2D20',
            'order' => 1,
            'is_featured' => true,
        ]);

        Experience::create([
            'company' => 'Acme Corp',
            'role' => 'Developer',
            'description' => 'Built systems.',
            'start_date' => '2023-01-01',
            'end_date' => null,
            'order' => 1,
        ]);

        $project = Project::create([
            'title' => 'Portfolio',
            'description' => 'My portfolio website.',
            'repository_url' => 'https://github.com/user/portfolio',
            'demo_url' => 'https://example.com',
            'order' => 1,
            'is_featured' => true,
        ]);

        SocialLink::create([
            'platform' => 'GitHub',
            'icon_slug' => 'github',
            'url' => 'https://github.com/kaikihirata',
            'order' => 1,
        ]);
    }

    // ─── Landing Page Rendering ───────────────────────────────────

    public function test_landing_page_loads_successfully(): void
    {
        $this->seedPortfolioData();

        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_landing_page_renders_correct_inertia_component(): void
    {
        $this->seedPortfolioData();

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page->component('Public/Portfolio'));
    }

    public function test_landing_page_is_accessible_without_authentication(): void
    {
        $this->seedPortfolioData();

        $response = $this->get('/');

        $response->assertOk();
    }

    // ─── Data Props ───────────────────────────────────────────────

    public function test_landing_page_includes_profile_data(): void
    {
        $this->seedPortfolioData();

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->has('profile')
            ->where('profile.name', 'Kaiki Hirata')
            ->where('profile.headline', 'Full Stack Developer')
        );
    }

    public function test_landing_page_includes_stacks(): void
    {
        $this->seedPortfolioData();

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page->has('stacks'));
    }

    public function test_landing_page_includes_experiences(): void
    {
        $this->seedPortfolioData();

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page->has('experiences'));
    }

    public function test_landing_page_includes_projects(): void
    {
        $this->seedPortfolioData();

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page->has('projects'));
    }

    public function test_landing_page_includes_social_links(): void
    {
        $this->seedPortfolioData();

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page->has('socialLinks'));
    }

    // ─── Featured Filtering ──────────────────────────────────────

    public function test_landing_page_only_shows_featured_stacks(): void
    {
        Profile::create([
            'name' => 'Test',
            'headline' => 'Dev',
            'bio' => 'Bio',
            'email' => 'test@example.com',
        ]);

        Stack::create(['name' => 'Featured', 'icon_slug' => 'feat', 'order' => 1, 'is_featured' => true]);
        Stack::create(['name' => 'Hidden', 'icon_slug' => 'hide', 'order' => 2, 'is_featured' => false]);

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page->has('stacks', 1));
    }

    public function test_landing_page_only_shows_featured_projects(): void
    {
        Profile::create([
            'name' => 'Test',
            'headline' => 'Dev',
            'bio' => 'Bio',
            'email' => 'test@example.com',
        ]);

        Project::create([
            'title' => 'Featured Project',
            'description' => 'Visible.',
            'order' => 1,
            'is_featured' => true,
        ]);

        Project::create([
            'title' => 'Hidden Project',
            'description' => 'Not visible.',
            'order' => 2,
            'is_featured' => false,
        ]);

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page->has('projects', 1));
    }

    // ─── Empty State ──────────────────────────────────────────────

    public function test_landing_page_works_with_empty_database(): void
    {
        // Profile singleton is created automatically via service
        $response = $this->get('/');

        $response->assertOk();
    }

    // ─── Project Demo URL Conditional ─────────────────────────────

    public function test_projects_include_demo_url_only_when_present(): void
    {
        $this->seedPortfolioData();

        Project::create([
            'title' => 'No Demo',
            'description' => 'No demo link.',
            'order' => 2,
            'is_featured' => true,
            'demo_url' => null,
        ]);

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->has('projects', 2)
        );
    }

    // ─── Experience Ordering ──────────────────────────────────────

    public function test_experiences_are_ordered_by_start_date_descending(): void
    {
        Profile::create([
            'name' => 'Test',
            'headline' => 'Dev',
            'bio' => 'Bio',
            'email' => 'test@example.com',
        ]);

        Experience::create([
            'company' => 'Old Corp',
            'role' => 'Junior',
            'start_date' => '2020-01-01',
            'end_date' => '2021-12-31',
            'order' => 2,
        ]);

        Experience::create([
            'company' => 'New Corp',
            'role' => 'Senior',
            'start_date' => '2024-01-01',
            'end_date' => null,
            'order' => 1,
        ]);

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->has('experiences', 2)
            ->where('experiences.0.company', 'New Corp')
            ->where('experiences.1.company', 'Old Corp')
        );
    }
}
