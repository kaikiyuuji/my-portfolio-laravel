<?php

namespace Tests\Feature\Public;

use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies that translatable fields are serialized as full JSON arrays
 * `{pt, en}` to the frontend so that the locale toggle in the UI can
 * pick the active translation client-side without re-requesting.
 *
 * Regression target: the toArray() override in Profile/Experience/Project/Post
 * models must keep returning getTranslations() instead of the current-locale
 * string that Spatie returns by default.
 */
class PortfolioTranslatableTest extends TestCase
{
    use RefreshDatabase;

    // ─── Profile ──────────────────────────────────────────────────

    public function test_profile_headline_is_serialized_as_array(): void
    {
        $profile = Profile::firstOrCreate([], [
            'name' => 'Kaiki',
            'email' => 'k@example.com',
            'headline' => '',
            'bio' => '',
        ]);
        $profile->setTranslation('headline', 'pt', 'Desenvolvedor Full Stack');
        $profile->setTranslation('headline', 'en', 'Full Stack Developer');
        $profile->setTranslation('bio', 'pt', 'Construindo software.');
        $profile->setTranslation('bio', 'en', 'Building software.');
        $profile->save();

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->where('profile.headline.pt', 'Desenvolvedor Full Stack')
            ->where('profile.headline.en', 'Full Stack Developer')
            ->where('profile.bio.pt', 'Construindo software.')
            ->where('profile.bio.en', 'Building software.')
        );
    }

    // ─── Experience ───────────────────────────────────────────────

    public function test_experience_translatable_fields_are_serialized_as_arrays(): void
    {
        Profile::firstOrCreate([], ['name' => 'X', 'email' => 'x@example.com', 'headline' => '', 'bio' => '']);

        $exp = Experience::create([
            'company' => ['pt' => 'Empresa Teste', 'en' => 'Test Company'],
            'role' => ['pt' => 'Desenvolvedor', 'en' => 'Developer'],
            'description' => ['pt' => 'Descrição.', 'en' => 'Description.'],
            'start_date' => '2024-01-01',
            'end_date' => null,
            'order' => 1,
        ]);

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->where('experiences.0.company.pt', 'Empresa Teste')
            ->where('experiences.0.company.en', 'Test Company')
            ->where('experiences.0.role.pt', 'Desenvolvedor')
            ->where('experiences.0.role.en', 'Developer')
            ->where('experiences.0.description.pt', 'Descrição.')
            ->where('experiences.0.description.en', 'Description.')
        );
    }

    // ─── Project ──────────────────────────────────────────────────

    public function test_project_translatable_fields_are_serialized_as_arrays(): void
    {
        Profile::firstOrCreate([], ['name' => 'X', 'email' => 'x@example.com', 'headline' => '', 'bio' => '']);

        Project::create([
            'title' => ['pt' => 'Meu Projeto', 'en' => 'My Project'],
            'description' => ['pt' => 'Descrição PT.', 'en' => 'Description EN.'],
            'order' => 1,
            'is_featured' => true,
        ]);

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->where('projects.0.title.pt', 'Meu Projeto')
            ->where('projects.0.title.en', 'My Project')
            ->where('projects.0.description.pt', 'Descrição PT.')
            ->where('projects.0.description.en', 'Description EN.')
        );
    }

    // ─── Fallback ─────────────────────────────────────────────────

    public function test_field_with_only_pt_returns_object_with_only_pt_key(): void
    {
        $profile = Profile::firstOrCreate([], [
            'name' => 'Y',
            'email' => 'y@example.com',
            'headline' => '',
            'bio' => '',
        ]);
        $profile->setTranslation('headline', 'pt', 'Apenas PT');
        $profile->save();

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->where('profile.headline.pt', 'Apenas PT')
            ->missing('profile.headline.fr')
        );
    }
}
