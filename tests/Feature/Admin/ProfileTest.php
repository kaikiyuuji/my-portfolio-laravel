<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    // ─── Acesso à Página de Edição do Perfil ──────────────────────

    public function test_authenticated_admin_can_access_profile_edit_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin/profile');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Profile/Edit'));
    }

    public function test_unauthenticated_user_cannot_access_profile_edit_page(): void
    {
        $response = $this->get('/admin/profile');

        $response->assertRedirect('/login');
    }

    // ─── Atualização dos Dados do Perfil ──────────────────────────

    public function test_admin_can_update_profile_information(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/admin/profile', [
                'name' => 'Kaiki Hirata',
                'headline' => 'Full Stack Developer',
                'bio' => 'Passionate developer building amazing things.',
                'email' => 'kaiki@example.com',
                'resume_url' => 'https://example.com/resume.pdf',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('profiles', [
            'name' => 'Kaiki Hirata',
            'headline' => 'Full Stack Developer',
            'email' => 'kaiki@example.com',
        ]);
    }

    public function test_profile_update_requires_name(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/admin/profile', [
                'name' => '',
                'headline' => 'Developer',
                'bio' => 'Some bio text.',
                'email' => 'kaiki@example.com',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_profile_update_requires_valid_email(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/admin/profile', [
                'name' => 'Kaiki',
                'headline' => 'Developer',
                'bio' => 'Some bio text.',
                'email' => 'not-a-valid-email',
            ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_profile_update_requires_headline(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/admin/profile', [
                'name' => 'Kaiki',
                'headline' => '',
                'bio' => 'Some bio text.',
                'email' => 'kaiki@example.com',
            ]);

        $response->assertSessionHasErrors('headline');
    }

    // ─── Upload de Avatar ─────────────────────────────────────────

    public function test_admin_can_upload_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/admin/profile', [
                'name' => 'Kaiki Hirata',
                'headline' => 'Developer',
                'bio' => 'Bio text.',
                'email' => 'kaiki@example.com',
                'avatar' => UploadedFile::fake()->image('avatar.jpg', 400, 400),
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseMissing('profiles', [
            'avatar_path' => null,
        ]);
    }

    public function test_avatar_upload_rejects_non_image_files(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/admin/profile', [
                'name' => 'Kaiki Hirata',
                'headline' => 'Developer',
                'bio' => 'Bio text.',
                'email' => 'kaiki@example.com',
                'avatar' => UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf'),
            ]);

        $response->assertSessionHasErrors('avatar');
    }

    public function test_avatar_upload_rejects_oversized_files(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/admin/profile', [
                'name' => 'Kaiki Hirata',
                'headline' => 'Developer',
                'bio' => 'Bio text.',
                'email' => 'kaiki@example.com',
                'avatar' => UploadedFile::fake()->image('avatar.jpg')->size(5120), // 5MB
            ]);

        $response->assertSessionHasErrors('avatar');
    }

    // ─── Resume URL ───────────────────────────────────────────────

    public function test_resume_url_must_be_valid_url_when_provided(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/admin/profile', [
                'name' => 'Kaiki',
                'headline' => 'Developer',
                'bio' => 'Bio text.',
                'email' => 'kaiki@example.com',
                'resume_url' => 'not-a-valid-url',
            ]);

        $response->assertSessionHasErrors('resume_url');
    }

    public function test_resume_url_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/admin/profile', [
                'name' => 'Kaiki',
                'headline' => 'Developer',
                'bio' => 'Bio text.',
                'email' => 'kaiki@example.com',
                'resume_url' => null,
            ]);

        $response->assertSessionHasNoErrors();
    }
}
