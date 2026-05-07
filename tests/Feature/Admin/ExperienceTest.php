<?php

namespace Tests\Feature\Admin;

use App\Models\Experience;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExperienceTest extends TestCase
{
    use RefreshDatabase;

    private function createExperience(array $overrides = []): Experience
    {
        return Experience::create(array_merge([
            'company' => 'Acme Corp',
            'role' => 'Backend Developer',
            'description' => 'Built APIs and microservices.',
            'start_date' => '2023-01-01',
            'end_date' => '2024-06-30',
            'order' => 1,
        ], $overrides));
    }

    // ─── Index ────────────────────────────────────────────────────

    public function test_authenticated_admin_can_view_experiences_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/experiences');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Experiences/Index'));
    }

    public function test_unauthenticated_user_cannot_access_experiences(): void
    {
        $this->get('/admin/experiences')->assertRedirect('/login');
    }

    // ─── Create ───────────────────────────────────────────────────

    public function test_admin_can_view_experience_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/experiences/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Experiences/Form'));
    }

    // ─── Store ────────────────────────────────────────────────────

    public function test_admin_can_create_an_experience(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/experiences', [
            'company' => 'Google',
            'role' => 'Software Engineer',
            'description' => 'Worked on search infrastructure.',
            'start_date' => '2022-03-01',
            'end_date' => '2024-12-31',
            'order' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('experiences', ['company' => 'Google', 'role' => 'Software Engineer']);
    }

    public function test_experience_requires_company(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/experiences', [
            'company' => '',
            'role' => 'Developer',
            'start_date' => '2023-01-01',
        ]);

        $response->assertSessionHasErrors('company');
    }

    public function test_experience_requires_role(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/experiences', [
            'company' => 'Acme',
            'role' => '',
            'start_date' => '2023-01-01',
        ]);

        $response->assertSessionHasErrors('role');
    }

    public function test_experience_requires_start_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/experiences', [
            'company' => 'Acme',
            'role' => 'Developer',
            'start_date' => '',
        ]);

        $response->assertSessionHasErrors('start_date');
    }

    public function test_experience_end_date_must_be_after_start_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/experiences', [
            'company' => 'Acme',
            'role' => 'Developer',
            'start_date' => '2024-01-01',
            'end_date' => '2023-01-01',
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_experience_end_date_null_means_current_job(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/experiences', [
            'company' => 'Current Corp',
            'role' => 'Lead Developer',
            'description' => 'Leading the team.',
            'start_date' => '2024-01-01',
            'end_date' => null,
            'order' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('experiences', [
            'company' => 'Current Corp',
            'end_date' => null,
        ]);
    }

    // ─── Edit ─────────────────────────────────────────────────────

    public function test_admin_can_view_experience_edit_form(): void
    {
        $user = User::factory()->create();
        $exp = $this->createExperience();

        $response = $this->actingAs($user)->get("/admin/experiences/{$exp->id}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Experiences/Form')->has('experience'));
    }

    // ─── Update ───────────────────────────────────────────────────

    public function test_admin_can_update_an_experience(): void
    {
        $user = User::factory()->create();
        $exp = $this->createExperience();

        $response = $this->actingAs($user)->put("/admin/experiences/{$exp->id}", [
            'company' => 'Updated Corp',
            'role' => 'Senior Developer',
            'description' => 'New description.',
            'start_date' => '2023-01-01',
            'end_date' => '2025-01-01',
            'order' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('experiences', ['id' => $exp->id, 'company' => 'Updated Corp']);
    }

    // ─── Destroy ──────────────────────────────────────────────────

    public function test_admin_can_delete_an_experience(): void
    {
        $user = User::factory()->create();
        $exp = $this->createExperience();

        $response = $this->actingAs($user)->delete("/admin/experiences/{$exp->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('experiences', ['id' => $exp->id]);
    }

    public function test_deleting_nonexistent_experience_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete('/admin/experiences/9999')->assertNotFound();
    }

    // ─── Ordering ─────────────────────────────────────────────────

    public function test_experiences_description_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/experiences', [
            'company' => 'Minimal Corp',
            'role' => 'Intern',
            'start_date' => '2023-06-01',
            'end_date' => '2023-12-31',
            'order' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('experiences', ['company' => 'Minimal Corp', 'description' => null]);
    }
}
