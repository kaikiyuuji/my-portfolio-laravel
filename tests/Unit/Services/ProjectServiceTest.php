<?php

namespace Tests\Unit\Services;

use App\Models\Project;
use App\Models\Stack;
use App\Services\ImageUploadService;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeService(?ImageUploadService $imageService = null): ProjectService
    {
        return new ProjectService(
            $imageService ?? Mockery::mock(ImageUploadService::class)
        );
    }

    private function createProject(array $overrides = []): Project
    {
        return Project::create(array_merge([
            'title' => ['pt' => 'Portfolio'],
            'description' => ['pt' => 'My portfolio.'],
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

    // ─── all() ────────────────────────────────────────────────────

    public function test_all_returns_projects_with_stacks_loaded(): void
    {
        $project = $this->createProject();
        $stack = $this->createStack();
        $project->stacks()->attach($stack->id);

        $service = $this->makeService();
        $result = $service->all();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->relationLoaded('stacks'));
    }

    // ─── store() ──────────────────────────────────────────────────

    public function test_store_creates_project_and_syncs_stacks(): void
    {
        $s1 = $this->createStack(['name' => 'PHP', 'icon_slug' => 'php']);
        $s2 = $this->createStack(['name' => 'Vue', 'icon_slug' => 'vue', 'order' => 2]);

        $service = $this->makeService();
        $project = $service->store([
            'title' => ['pt' => 'New App'],
            'description' => ['pt' => 'Built with PHP and Vue.'],
            'order' => 1,
            'is_featured' => true,
        ], [$s1->id, $s2->id]);

        $this->assertInstanceOf(Project::class, $project);
        $this->assertDatabaseHas('projects', ['title->pt' => 'New App']);
        $this->assertCount(2, $project->stacks);
    }

    public function test_store_works_with_empty_stack_ids(): void
    {
        $service = $this->makeService();
        $project = $service->store([
            'title' => ['pt' => 'Solo Project'],
            'description' => ['pt' => 'No stacks.'],
            'order' => 1,
            'is_featured' => false,
        ], []);

        $this->assertDatabaseHas('projects', ['title->pt' => 'Solo Project']);
        $this->assertCount(0, $project->stacks);
    }

    // ─── update() ─────────────────────────────────────────────────

    public function test_update_modifies_project_and_resyncs_stacks(): void
    {
        $project = $this->createProject();
        $s1 = $this->createStack(['name' => 'Old', 'icon_slug' => 'old']);
        $project->stacks()->attach($s1->id);

        $s2 = $this->createStack(['name' => 'New', 'icon_slug' => 'new', 'order' => 2]);

        $service = $this->makeService();
        $result = $service->update($project, [
            'title' => ['pt' => 'Updated'],
            'description' => ['pt' => 'Updated desc.'],
        ], [$s2->id]);

        $this->assertEquals('Updated', $result->getTranslation('title', 'pt'));
        $result->refresh();
        $this->assertCount(1, $result->stacks);
        $this->assertEquals($s2->id, $result->stacks->first()->id);
    }

    // ─── updateImage() ────────────────────────────────────────────

    public function test_update_image_stores_file_and_updates_path(): void
    {
        Storage::fake('public');

        $project = $this->createProject(['image_path' => null]);

        $mockUploadService = Mockery::mock(ImageUploadService::class);
        $mockUploadService->shouldReceive('delete')->once()->with(null);
        $mockUploadService->shouldReceive('store')
            ->once()
            ->andReturn('projects/new-image.jpg');

        $service = $this->makeService($mockUploadService);
        $file = UploadedFile::fake()->image('project.jpg', 1200, 800);
        $path = $service->updateImage($project, $file);

        $this->assertEquals('projects/new-image.jpg', $path);
    }

    // ─── destroy() ────────────────────────────────────────────────

    public function test_destroy_removes_project_and_detaches_stacks(): void
    {
        $project = $this->createProject();
        $stack = $this->createStack();
        $project->stacks()->attach($stack->id);

        $mockUploadService = Mockery::mock(ImageUploadService::class);
        $mockUploadService->shouldReceive('delete')->once();

        $service = $this->makeService($mockUploadService);
        $service->destroy($project);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('project_technologies', ['project_id' => $project->id]);
    }

    public function test_destroy_deletes_image_from_storage(): void
    {
        Storage::fake('public');

        $project = $this->createProject(['image_path' => 'projects/test.jpg']);

        $mockUploadService = Mockery::mock(ImageUploadService::class);
        $mockUploadService->shouldReceive('delete')
            ->once()
            ->with('projects/test.jpg');

        $service = $this->makeService($mockUploadService);
        $service->destroy($project);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    // ─── reorder() ────────────────────────────────────────────────

    public function test_reorder_updates_order_column(): void
    {
        $a = $this->createProject(['title' => ['pt' => 'A'], 'order' => 1]);
        $b = $this->createProject(['title' => ['pt' => 'B'], 'order' => 2]);

        $service = $this->makeService();
        $service->reorder([$b->id, $a->id]);

        $this->assertDatabaseHas('projects', ['id' => $b->id, 'order' => 1]);
        $this->assertDatabaseHas('projects', ['id' => $a->id, 'order' => 2]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
