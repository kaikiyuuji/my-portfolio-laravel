<?php

namespace Tests\Feature\Admin;

use App\Models\Stack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StackTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_authenticated_admin_can_view_stacks_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/stacks');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Stacks/Index'));
    }

    public function test_unauthenticated_user_cannot_access_stacks_index(): void
    {
        $this->get('/admin/stacks')->assertRedirect('/login');
    }

    public function test_stacks_index_shows_all_stacks(): void
    {
        $user = User::factory()->create();
        $this->createStack(['name' => 'Laravel', 'icon_slug' => 'laravel', 'order' => 1]);
        $this->createStack(['name' => 'Vue.js', 'icon_slug' => 'vuedotjs', 'order' => 2]);

        $response = $this->actingAs($user)->get('/admin/stacks');

        $response->assertInertia(fn ($page) => $page->has('stacks', 2));
    }

    public function test_authenticated_admin_can_view_stack_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/stacks/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Stacks/Form'));
    }

    public function test_admin_can_create_a_stack(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/stacks', [
            'name' => 'PHP',
            'icon_slug' => 'php',
            'color' => '#777BB4',
            'order' => 1,
            'is_featured' => true,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('stacks', ['name' => 'PHP', 'icon_slug' => 'php']);
    }

    public function test_stack_creation_requires_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/stacks', [
            'name' => '',
            'icon_slug' => 'php',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_stack_creation_requires_icon_slug(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/stacks', [
            'name' => 'PHP',
            'icon_slug' => '',
        ]);

        $response->assertSessionHasErrors('icon_slug');
    }

    public function test_stack_icon_slug_must_be_unique(): void
    {
        $user = User::factory()->create();
        $this->createStack(['icon_slug' => 'laravel']);

        $response = $this->actingAs($user)->post('/admin/stacks', [
            'name' => 'Laravel Dup',
            'icon_slug' => 'laravel',
        ]);

        $response->assertSessionHasErrors('icon_slug');
    }

    public function test_admin_can_view_stack_edit_form(): void
    {
        $user = User::factory()->create();
        $stack = $this->createStack();

        $response = $this->actingAs($user)->get("/admin/stacks/{$stack->id}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Stacks/Form')->has('stack'));
    }

    public function test_admin_can_update_a_stack(): void
    {
        $user = User::factory()->create();
        $stack = $this->createStack(['name' => 'Old Name']);

        $response = $this->actingAs($user)->put("/admin/stacks/{$stack->id}", [
            'name' => 'New Name',
            'icon_slug' => 'laravel',
            'color' => '#FF0000',
            'order' => 2,
            'is_featured' => false,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('stacks', ['id' => $stack->id, 'name' => 'New Name']);
    }

    public function test_stack_update_allows_same_icon_slug_for_same_stack(): void
    {
        $user = User::factory()->create();
        $stack = $this->createStack(['icon_slug' => 'laravel']);

        $response = $this->actingAs($user)->put("/admin/stacks/{$stack->id}", [
            'name' => 'Laravel Updated',
            'icon_slug' => 'laravel',
            'color' => '#FF2D20',
            'order' => 1,
            'is_featured' => true,
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_admin_can_delete_a_stack(): void
    {
        $user = User::factory()->create();
        $stack = $this->createStack();

        $response = $this->actingAs($user)->delete("/admin/stacks/{$stack->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('stacks', ['id' => $stack->id]);
    }

    public function test_deleting_nonexistent_stack_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete('/admin/stacks/9999')->assertNotFound();
    }

    public function test_admin_can_reorder_stacks(): void
    {
        $user = User::factory()->create();
        $a = $this->createStack(['name' => 'A', 'icon_slug' => 'a', 'order' => 1]);
        $b = $this->createStack(['name' => 'B', 'icon_slug' => 'b', 'order' => 2]);

        $response = $this->actingAs($user)->put('/admin/stacks/reorder', [
            'ordered_ids' => [$b->id, $a->id],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('stacks', ['id' => $a->id, 'order' => 2]);
        $this->assertDatabaseHas('stacks', ['id' => $b->id, 'order' => 1]);
    }

    public function test_is_featured_defaults_to_false(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/stacks', [
            'name' => 'MySQL',
            'icon_slug' => 'mysql',
            'color' => '#4479A1',
            'order' => 1,
        ]);

        $this->assertDatabaseHas('stacks', ['name' => 'MySQL', 'is_featured' => false]);
    }
}
