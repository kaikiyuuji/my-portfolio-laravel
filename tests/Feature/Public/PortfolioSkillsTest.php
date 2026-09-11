<?php

namespace Tests\Feature\Public;

use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PortfolioSkillsTest extends TestCase
{
    use RefreshDatabase;

    public function test_portfolio_handles_empty_skills(): void
    {
        $this->get(route('home'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/Portfolio')
            ->has('skills', 0)
        );
    }

    public function test_portfolio_only_exposes_visible_skills_ordered_with_both_translations(): void
    {
        $technical = Skill::create([
            'title' => ['pt' => 'Montagem de computadores', 'en' => 'Computer assembly'],
            'description' => ['pt' => 'Montagem e desmontagem.', 'en' => 'Assembly and disassembly.'],
            'category' => 'technical',
            'order' => 10,
        ]);
        $interpersonal = Skill::create([
            'title' => ['pt' => 'Organização', 'en' => 'Organization'],
            'category' => 'interpersonal',
            'order' => 1,
        ]);
        Skill::create([
            'title' => ['pt' => 'Oculta', 'en' => 'Hidden'],
            'category' => 'technical',
            'order' => 0,
            'is_visible' => false,
        ]);

        $this->get(route('home'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/Portfolio')
            ->has('skills', 2)
            ->where('skills.0.id', $interpersonal->id)
            ->where('skills.0.category', 'interpersonal')
            ->where('skills.1.id', $technical->id)
            ->where('skills.1.title.pt', 'Montagem de computadores')
            ->where('skills.1.title.en', 'Computer assembly')
            ->where('skills.1.description.pt', 'Montagem e desmontagem.')
            ->where('skills.1.description.en', 'Assembly and disassembly.')
            ->missing('skills.1.seed_key')
        );
    }
}
