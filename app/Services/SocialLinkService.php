<?php

namespace App\Services;

use App\Models\SocialLink;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SocialLinkService
{
    public function all(): Collection
    {
        return SocialLink::orderBy('order')->get();
    }

    public function store(array $data): SocialLink
    {
        return SocialLink::create($data);
    }

    public function update(SocialLink $link, array $data): SocialLink
    {
        $link->update($data);

        return $link->fresh();
    }

    public function destroy(SocialLink $link): void
    {
        $link->delete();
    }

    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                SocialLink::where('id', $id)->update(['order' => $index + 1]);
            }
        });
    }
}
