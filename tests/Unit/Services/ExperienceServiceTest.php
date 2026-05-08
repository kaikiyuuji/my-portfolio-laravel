<?php

namespace Tests\Unit\Services;

use App\Models\Experience;
use App\Services\ExperienceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExperienceServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeService(): ExperienceService
    {
        return new ExperienceService();
    }

    private function createExperience(array $overrides = []): Experience
    {
        return Experience::create(array_merge([
            'company' => ['pt' => 'Acme Corp'],
            'role' => ['pt' => 'Developer'],
            'description' => ['pt' => 'Built APIs.'],
            'start_date' => '2023-01-01',
            'end_date' => '2024-06-30',
            'order' => 1,
        ], $overrides));
    }

    // ─── all() ────────────────────────────────────────────────────

    public function test_all_returns_experiences_ordered_by_start_date_desc(): void
    {
        $this->createExperience(['company' => ['pt' => 'Old'], 'start_date' => '2020-01-01', 'order' => 2]);
        $this->createExperience(['company' => ['pt' => 'New'], 'start_date' => '2024-01-01', 'order' => 1]);

        $service = $this->makeService();
        $result = $service->all();

        $this->assertEquals('New', $result->first()->getTranslation('company', 'pt'));
        $this->assertEquals('Old', $result->last()->getTranslation('company', 'pt'));
    }

    // ─── store() ──────────────────────────────────────────────────

    public function test_store_creates_new_experience(): void
    {
        $service = $this->makeService();
        $exp = $service->store([
            'company' => ['pt' => 'Google'],
            'role' => ['pt' => 'SWE'],
            'description' => ['pt' => 'Search infra.'],
            'start_date' => '2022-03-01',
            'end_date' => null,
            'order' => 1,
        ]);

        $this->assertInstanceOf(Experience::class, $exp);
        $this->assertDatabaseHas('experiences', ['company->pt' => 'Google']);
    }

    // ─── update() ─────────────────────────────────────────────────

    public function test_update_modifies_existing_experience(): void
    {
        $exp = $this->createExperience();

        $service = $this->makeService();
        $result = $service->update($exp, [
            'company' => ['pt' => 'Updated Corp'],
            'role' => ['pt' => 'Senior'],
            'start_date' => '2023-01-01',
        ]);

        $this->assertEquals('Updated Corp', $result->getTranslation('company', 'pt'));
        $this->assertDatabaseHas('experiences', ['id' => $exp->id, 'company->pt' => 'Updated Corp']);
    }

    // ─── destroy() ────────────────────────────────────────────────

    public function test_destroy_removes_experience(): void
    {
        $exp = $this->createExperience();

        $service = $this->makeService();
        $service->destroy($exp);

        $this->assertDatabaseMissing('experiences', ['id' => $exp->id]);
    }
}
