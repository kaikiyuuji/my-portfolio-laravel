<?php

namespace App\Services;

use App\Models\Stack;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StackService
{
    public function all(): Collection
    {
        return Stack::orderBy('order')->get();
    }

    public function featured(): Collection
    {
        return Stack::where('is_featured', true)->orderBy('order')->get();
    }

    public function store(array $data): Stack
    {
        return Stack::create($data);
    }

    public function update(Stack $stack, array $data): Stack
    {
        $stack->update($data);

        return $stack->fresh();
    }

    public function destroy(Stack $stack): void
    {
        $stack->delete();
    }

    /**
     * Reorder stacks based on the given array of IDs.
     * Position in the array determines the new `order` (1-based).
     */
    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                Stack::where('id', $id)->update(['order' => $index + 1]);
            }
        });
    }
}
