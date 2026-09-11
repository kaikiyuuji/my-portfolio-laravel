<?php

namespace Tests\Unit\Services;

use App\Models\Skill;
use App\Services\SkillService;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createSkill(array $overrides = []): Skill
    {
        return Skill::create(array_replace([
            'title' => ['pt' => 'Competência', 'en' => 'Skill'],
            'category' => 'technical',
            'order' => 0,
            'is_visible' => true,
        ], $overrides));
    }

    public function test_visible_collection_excludes_hidden_skills_and_uses_stable_order(): void
    {
        $last = $this->createSkill(['order' => 9]);
        $first = $this->createSkill(['order' => 2]);
        $second = $this->createSkill(['order' => 2]);
        $hidden = $this->createSkill(['order' => 0, 'is_visible' => false]);
        $service = app(SkillService::class);

        $this->assertSame([$first->id, $second->id, $last->id], $service->visible()->pluck('id')->all());
        $this->assertSame([$hidden->id, $first->id, $second->id, $last->id], $service->all()->pluck('id')->all());
    }

    public function test_seeder_adds_only_requested_competencies_with_translations_and_categories(): void
    {
        $this->seed(SkillSeeder::class);

        $this->assertDatabaseCount('skills', 11);
        $this->assertSame(8, Skill::where('category', 'technical')->count());
        $this->assertSame(3, Skill::where('category', 'interpersonal')->count());
        $this->assertSame(range(0, 10), Skill::ordered()->pluck('order')->all());
        $this->assertSame(11, Skill::visible()->count());

        $this->assertDatabaseHas('skills', [
            'seed_key' => 'computer-assembly',
            'title->pt' => 'Montagem e desmontagem de computadores',
            'title->en' => 'Computer assembly and disassembly',
        ]);
        $this->assertDatabaseHas('skills', [
            'seed_key' => 'word-excel',
            'title->pt' => 'Microsoft Word (Avançado) e Excel (Intermediário)',
        ]);

        foreach (Skill::all() as $skill) {
            $this->assertNotEmpty($skill->getTranslation('title', 'pt', false));
            $this->assertNotEmpty($skill->getTranslation('title', 'en', false));
            $this->assertArrayNotHasKey('seed_key', $skill->toArray());
        }
    }

    public function test_reseeding_preserves_renamed_skills_and_all_user_edits_without_duplicates(): void
    {
        $this->seed(SkillSeeder::class);
        $skill = Skill::where('seed_key', 'computer-assembly')->sole();
        $skill->update([
            'title' => ['pt' => 'Título editado', 'en' => 'Edited title'],
            'description' => ['pt' => 'Minha descrição', 'en' => 'My description'],
            'category' => 'interpersonal',
            'order' => 999,
            'is_visible' => false,
        ]);
        $custom = $this->createSkill(['title' => ['pt' => 'Adicionada pelo usuário']]);
        $original = $skill->fresh()->getAttributes();

        $this->seed(SkillSeeder::class);
        $this->seed(SkillSeeder::class);

        $this->assertDatabaseCount('skills', 12);
        $this->assertSame($original, $skill->fresh()->getAttributes());
        $this->assertSame(1, Skill::where('seed_key', 'computer-assembly')->count());
        $this->assertSame('Adicionada pelo usuário', $custom->fresh()->getTranslation('title', 'pt'));
    }
}
