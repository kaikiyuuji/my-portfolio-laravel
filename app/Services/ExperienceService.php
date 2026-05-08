<?php

namespace App\Services;

use App\Models\Experience;
use Illuminate\Support\Collection;

class ExperienceService
{
    public function all(): Collection
    {
        return Experience::ordered()->get();
    }

    public function store(array $data): Experience
    {
        return Experience::create($data);
    }

    public function update(Experience $experience, array $data): Experience
    {
        $experience->update($data);

        return $experience->fresh();
    }

    public function destroy(Experience $experience): void
    {
        $experience->delete();
    }
}
