<?php

namespace Tests\Unit\Services;

use App\Models\Stack;
use App\Services\StackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StackServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeService(): StackService
    {
        return new StackService();
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

    // ─── all() ────────────────────────────────────────────────────

    public function test_all_returns_stacks_ordered_by_order(): void
    {
        $this->createStack(['name' => 'Vue', 'icon_slug' => 'vue', 'order' => 2]);
        $this->createStack(['name' => 'PHP', 'icon_slug' => 'php', 'order' => 1]);

        $service = $this->makeService();
        $result = $service->all();

        $this->assertEquals('PHP', $result->first()->name);
        $this->assertEquals('Vue', $result->last()->name);
    }

    // ─── featured() ───────────────────────────────────────────────

    public function test_featured_returns_only_featured_stacks(): void
    {
        $this->createStack(['name' => 'Featured', 'icon_slug' => 'feat', 'is_featured' => true]);
        $this->createStack(['name' => 'Hidden', 'icon_slug' => 'hide', 'is_featured' => false, 'order' => 2]);

        $service = $this->makeService();
        $result = $service->featured();

        $this->assertCount(1, $result);
        $this->assertEquals('Featured', $result->first()->name);
    }

    // ─── store() ──────────────────────────────────────────────────

    public function test_store_creates_a_new_stack(): void
    {
        $service = $this->makeService();
        $stack = $service->store([
            'name' => 'Docker',
            'icon_slug' => 'docker',
            'color' => '#2496ED',
            'order' => 1,
            'is_featured' => false,
        ]);

        $this->assertInstanceOf(Stack::class, $stack);
        $this->assertDatabaseHas('stacks', ['name' => 'Docker']);
    }

    // ─── update() ─────────────────────────────────────────────────

    public function test_update_modifies_existing_stack(): void
    {
        $stack = $this->createStack(['name' => 'Old']);

        $service = $this->makeService();
        $result = $service->update($stack, ['name' => 'New', 'icon_slug' => 'laravel', 'order' => 1]);

        $this->assertEquals('New', $result->name);
        $this->assertDatabaseHas('stacks', ['id' => $stack->id, 'name' => 'New']);
    }

    // ─── destroy() ────────────────────────────────────────────────

    public function test_destroy_removes_stack(): void
    {
        $stack = $this->createStack();

        $service = $this->makeService();
        $service->destroy($stack);

        $this->assertDatabaseMissing('stacks', ['id' => $stack->id]);
    }

    // ─── reorder() ────────────────────────────────────────────────

    public function test_reorder_updates_order_column(): void
    {
        $a = $this->createStack(['name' => 'A', 'icon_slug' => 'a', 'order' => 1]);
        $b = $this->createStack(['name' => 'B', 'icon_slug' => 'b', 'order' => 2]);

        $service = $this->makeService();
        $service->reorder([$b->id, $a->id]);

        $this->assertDatabaseHas('stacks', ['id' => $b->id, 'order' => 1]);
        $this->assertDatabaseHas('stacks', ['id' => $a->id, 'order' => 2]);
    }
}
