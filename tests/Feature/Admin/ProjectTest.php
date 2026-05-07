<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\Stack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private function createProject(array $overrides = []): Project
    {
        return Project::create(array_merge([
            'title' => 'Portfolio Website',
            'description' => 'My personal portfolio built with Laravel.',
            'image_path' => null,
            'repository_url' => 'https://github.com/user/portfolio',
            'demo_url' => 'https://example.com',
            'order' => 1,
            'is_featured' => true,
        ], $overrides));
    }

    private function createStack(array $overrides = []): Stack
    {
        return Stack::create(array_merge([
            'name' => 'Laravel',
            'icon_slug' => 'laravel',
            'color' => '#FF2D20',
            'order' => 1,
            'is_featured' => true,
        ], $overrides));
    }

    // ─── Index ────────────────────────────────────────────────────

    public function test_authenticated_admin_can_view_projects_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/projects');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Projects/Index'));
    }

    public function test_unauthenticated_user_cannot_access_projects(): void
    {
        $this->get('/admin/projects')->assertRedirect('/login');
    }

    // ─── Create ───────────────────────────────────────────────────

    public function test_admin_can_view_project_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/projects/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Projects/Form'));
    }

    // ─── Store ────────────────────────────────────────────────────

    public function test_admin_can_create_a_project(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/projects', [
            'title' => 'New Project',
            'description' => 'A new amazing project.',
            'repository_url' => 'https://github.com/user/new-project',
            'demo_url' => 'https://newproject.com',
            'order' => 1,
            'is_featured' => true,
            'stack_ids' => [],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('projects', ['title' => 'New Project']);
    }

    public function test_project_creation_requires_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/projects', [
            'title' => '',
            'description' => 'Some description.',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_project_creation_requires_description(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/projects', [
            'title' => 'My Project',
            'description' => '',
        ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_project_can_be_created_with_stacks(): void
    {
        $user = User::factory()->create();
        $s1 = $this->createStack(['name' => 'Laravel', 'icon_slug' => 'laravel']);
        $s2 = $this->createStack(['name' => 'Vue', 'icon_slug' => 'vuedotjs', 'order' => 2]);

        $response = $this->actingAs($user)->post('/admin/projects', [
            'title' => 'Full Stack App',
            'description' => 'Built with Laravel and Vue.',
            'order' => 1,
            'is_featured' => true,
            'stack_ids' => [$s1->id, $s2->id],
        ]);

        $response->assertSessionHasNoErrors();

        $project = Project::where('title', 'Full Stack App')->first();
        $this->assertNotNull($project);
        $this->assertCount(2, $project->stacks);
    }

    public function test_repository_url_must_be_valid_url(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/projects', [
            'title' => 'Test',
            'description' => 'Test desc.',
            'repository_url' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors('repository_url');
    }

    public function test_demo_url_must_be_valid_url(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/projects', [
            'title' => 'Test',
            'description' => 'Test desc.',
            'demo_url' => 'invalid',
        ]);

        $response->assertSessionHasErrors('demo_url');
    }

    public function test_demo_url_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/projects', [
            'title' => 'No Demo',
            'description' => 'Project without demo.',
            'order' => 1,
            'is_featured' => false,
            'stack_ids' => [],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('projects', ['title' => 'No Demo', 'demo_url' => null]);
    }

    // ─── Image Upload ─────────────────────────────────────────────

    public function test_admin_can_upload_project_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/projects', [
            'title' => 'With Image',
            'description' => 'Project with an image.',
            'order' => 1,
            'is_featured' => false,
            'stack_ids' => [],
            'image' => UploadedFile::fake()->image('project.jpg', 1200, 800),
        ]);

        $response->assertSessionHasNoErrors();

        $project = Project::where('title', 'With Image')->first();
        $this->assertNotNull($project->image_path);
    }

    public function test_project_image_rejects_non_image_files(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/projects', [
            'title' => 'Bad Image',
            'description' => 'Should fail.',
            'order' => 1,
            'stack_ids' => [],
            'image' => UploadedFile::fake()->create('doc.pdf', 1024, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('image');
    }

    // ─── Edit ─────────────────────────────────────────────────────

    public function test_admin_can_view_project_edit_form(): void
    {
        $user = User::factory()->create();
        $project = $this->createProject();

        $response = $this->actingAs($user)->get("/admin/projects/{$project->id}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Projects/Form')->has('project'));
    }

    // ─── Update ───────────────────────────────────────────────────

    public function test_admin_can_update_a_project(): void
    {
        $user = User::factory()->create();
        $project = $this->createProject();

        $response = $this->actingAs($user)->put("/admin/projects/{$project->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated description.',
            'repository_url' => 'https://github.com/user/updated',
            'order' => 1,
            'is_featured' => true,
            'stack_ids' => [],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'title' => 'Updated Title']);
    }

    public function test_admin_can_sync_project_stacks_on_update(): void
    {
        $user = User::factory()->create();
        $project = $this->createProject();
        $s1 = $this->createStack(['name' => 'PHP', 'icon_slug' => 'php']);
        $project->stacks()->attach($s1->id);

        $s2 = $this->createStack(['name' => 'MySQL', 'icon_slug' => 'mysql', 'order' => 2]);

        $response = $this->actingAs($user)->put("/admin/projects/{$project->id}", [
            'title' => $project->title,
            'description' => $project->description,
            'order' => 1,
            'is_featured' => true,
            'stack_ids' => [$s2->id],
        ]);

        $response->assertSessionHasNoErrors();
        $project->refresh();
        $this->assertCount(1, $project->stacks);
        $this->assertEquals($s2->id, $project->stacks->first()->id);
    }

    // ─── Destroy ──────────────────────────────────────────────────

    public function test_admin_can_delete_a_project(): void
    {
        $user = User::factory()->create();
        $project = $this->createProject();

        $response = $this->actingAs($user)->delete("/admin/projects/{$project->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_deleting_project_removes_image_from_storage(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $path = Storage::disk('public')->put('projects', UploadedFile::fake()->image('test.jpg'));
        $project = $this->createProject(['image_path' => $path]);

        $this->actingAs($user)->delete("/admin/projects/{$project->id}");

        Storage::disk('public')->assertMissing($path);
    }

    public function test_deleting_project_detaches_stacks(): void
    {
        $user = User::factory()->create();
        $project = $this->createProject();
        $stack = $this->createStack();
        $project->stacks()->attach($stack->id);

        $this->actingAs($user)->delete("/admin/projects/{$project->id}");

        $this->assertDatabaseMissing('project_technologies', ['project_id' => $project->id]);
    }

    // ─── Reorder ──────────────────────────────────────────────────

    public function test_admin_can_reorder_projects(): void
    {
        $user = User::factory()->create();
        $a = $this->createProject(['title' => 'A', 'order' => 1]);
        $b = $this->createProject(['title' => 'B', 'order' => 2]);

        $response = $this->actingAs($user)->put('/admin/projects/reorder', [
            'ordered_ids' => [$b->id, $a->id],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('projects', ['id' => $a->id, 'order' => 2]);
        $this->assertDatabaseHas('projects', ['id' => $b->id, 'order' => 1]);
    }
}
