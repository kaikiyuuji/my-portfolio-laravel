<?php

namespace Tests\Feature\Admin;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SkillTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'title' => ['pt' => 'Montagem de computadores', 'en' => 'Computer assembly'],
            'description' => ['pt' => 'Montagem e desmontagem.', 'en' => 'Assembly and disassembly.'],
            'category' => 'technical',
            'order' => 5,
            'is_visible' => true,
        ], $overrides);
    }

    public function test_all_admin_skill_routes_require_authentication(): void
    {
        $skill = Skill::create($this->payload());

        foreach ([
            ['get', '/admin/skills'],
            ['get', '/admin/skills/create'],
            ['get', "/admin/skills/{$skill->id}/edit"],
            ['post', '/admin/skills'],
            ['put', "/admin/skills/{$skill->id}"],
            ['delete', "/admin/skills/{$skill->id}"],
        ] as [$method, $url]) {
            $this->{$method}($url)->assertRedirect('/login');
        }

        $this->assertDatabaseCount('skills', 1);
    }

    public function test_admin_index_includes_hidden_skills_in_stable_order_and_forms_keep_translations(): void
    {
        $last = Skill::create($this->payload(['order' => 20]));
        $first = Skill::create($this->payload(['order' => 1, 'is_visible' => false]));
        $second = Skill::create($this->payload(['order' => 1]));
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.skills.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Skills/Index')
            ->has('skills', 3)
            ->where('skills.0.id', $first->id)
            ->where('skills.0.is_visible', false)
            ->where('skills.1.id', $second->id)
            ->where('skills.2.id', $last->id)
        );
        $this->get(route('admin.skills.create'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Skills/Form')
        );
        $this->get(route('admin.skills.edit', $first))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Skills/Form')
            ->where('skill.title.pt', 'Montagem de computadores')
            ->where('skill.title.en', 'Computer assembly')
            ->where('skill.description.pt', 'Montagem e desmontagem.')
            ->where('skill.description.en', 'Assembly and disassembly.')
            ->missing('skill.seed_key')
        );
    }

    public function test_admin_can_create_translated_skill_but_cannot_set_internal_seed_key(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.skills.store'), $this->payload([
            'seed_key' => 'computer-assembly',
        ]))->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Competência adicionada.')
            ->assertRedirect(route('admin.skills.index'));

        $skill = Skill::sole();
        $this->assertSame($this->payload()['title'], $skill->getTranslations('title'));
        $this->assertSame($this->payload()['description'], $skill->getTranslations('description'));
        $this->assertSame('technical', $skill->category);
        $this->assertSame(5, $skill->order);
        $this->assertTrue($skill->is_visible);
        $this->assertNull($skill->seed_key);
    }

    public function test_optional_fields_use_defaults_and_english_translation_is_optional(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.skills.store'), [
            'title' => ['pt' => 'Organização'],
            'category' => 'interpersonal',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.skills.index'));

        $skill = Skill::sole();
        $this->assertSame(['pt' => 'Organização'], $skill->getTranslations('title'));
        $this->assertSame([], $skill->getTranslations('description'));
        $this->assertSame(0, $skill->order);
        $this->assertTrue($skill->is_visible);
    }

    #[DataProvider('invalidFields')]
    public function test_invalid_skill_fields_are_rejected_on_create_and_update(array $overrides, array $errors): void
    {
        $skill = Skill::create($this->payload());
        $original = $skill->fresh()->toArray();
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.skills.store'), $this->payload($overrides))->assertSessionHasErrors($errors);
        $this->put(route('admin.skills.update', $skill), $this->payload($overrides))->assertSessionHasErrors($errors);

        $this->assertDatabaseCount('skills', 1);
        $this->assertSame($original, $skill->fresh()->toArray());
    }

    public static function invalidFields(): array
    {
        return [
            'missing Portuguese title' => [['title' => ['en' => 'Only English']], ['title.pt']],
            'blank title' => [['title' => ['pt' => '   ']], ['title.pt']],
            'non-array title' => [['title' => 'Not translated'], ['title']],
            'unsupported translation' => [['title' => ['pt' => 'Título', 'fr' => 'Titre']], ['title']],
            'long titles' => [['title' => ['pt' => str_repeat('a', 256), 'en' => str_repeat('b', 256)]], ['title.pt', 'title.en']],
            'long descriptions' => [['description' => ['pt' => str_repeat('a', 1001), 'en' => str_repeat('b', 1001)]], ['description.pt', 'description.en']],
            'invalid category' => [['category' => 'other'], ['category']],
            'negative order' => [['order' => -1], ['order']],
            'oversized order' => [['order' => 10000], ['order']],
            'fractional order' => [['order' => 1.5], ['order']],
            'invalid visibility' => [['is_visible' => 'invalid'], ['is_visible']],
        ];
    }

    public function test_admin_can_update_translations_category_order_and_visibility(): void
    {
        $skill = Skill::create($this->payload());

        $this->actingAs(User::factory()->create())->put(route('admin.skills.update', $skill), $this->payload([
            'title' => ['pt' => 'Trabalho em equipe', 'en' => 'Teamwork'],
            'description' => ['pt' => '', 'en' => ''],
            'category' => 'interpersonal',
            'order' => 9999,
            'is_visible' => '0',
            'seed_key' => 'fake',
        ]))->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Competência atualizada.')
            ->assertRedirect(route('admin.skills.index'));

        $skill->refresh();
        $this->assertSame(['pt' => 'Trabalho em equipe', 'en' => 'Teamwork'], $skill->getTranslations('title'));
        $this->assertEmpty($skill->getTranslation('description', 'pt'));
        $this->assertEmpty($skill->getTranslation('description', 'en'));
        $this->assertSame('interpersonal', $skill->category);
        $this->assertSame(9999, $skill->order);
        $this->assertFalse($skill->is_visible);
        $this->assertNull($skill->seed_key);
    }

    public function test_omitting_optional_update_fields_preserves_existing_values(): void
    {
        $skill = Skill::create($this->payload(['order' => 17, 'is_visible' => false]));

        $this->actingAs(User::factory()->create())->put(route('admin.skills.update', $skill), [
            'title' => ['pt' => 'Novo título'],
            'category' => 'technical',
        ])->assertSessionHasNoErrors();

        $skill->refresh();
        $this->assertSame(17, $skill->order);
        $this->assertFalse($skill->is_visible);
        $this->assertSame($this->payload()['description'], $skill->getTranslations('description'));
    }

    public function test_admin_can_delete_skill_and_missing_records_return_not_found(): void
    {
        $skill = Skill::create($this->payload());
        $this->actingAs(User::factory()->create());

        $this->delete(route('admin.skills.destroy', $skill))
            ->assertSessionHas('success', 'Competência removida.')
            ->assertRedirect(route('admin.skills.index'));
        $this->assertDatabaseMissing('skills', ['id' => $skill->id]);

        $this->get(route('admin.skills.edit', $skill))->assertNotFound();
        $this->put(route('admin.skills.update', $skill), $this->payload())->assertNotFound();
        $this->delete(route('admin.skills.destroy', $skill))->assertNotFound();
    }
}
