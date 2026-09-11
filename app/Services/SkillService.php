<?php

namespace App\Services;

use App\Models\Skill;
use Illuminate\Support\Collection;

class SkillService
{
    public function all(): Collection
    {
        return Skill::ordered()->get();
    }

    public function visible(): Collection
    {
        return Skill::visible()->ordered()->get();
    }

    public function store(array $data): Skill
    {
        return Skill::create($data);
    }

    public function update(Skill $skill, array $data): Skill
    {
        $skill->update($data);

        return $skill->fresh();
    }

    public function destroy(Skill $skill): void
    {
        $skill->delete();
    }
}
