<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStackRequest;
use App\Http\Requests\Admin\UpdateStackRequest;
use App\Models\Stack;
use App\Services\StackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StackController extends Controller
{
    public function __construct(
        private StackService $stackService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Stacks/Index', [
            'stacks' => $this->stackService->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Stacks/Form');
    }

    public function store(StoreStackRequest $request): RedirectResponse
    {
        $this->stackService->store($request->validated());

        return redirect()->route('admin.stacks.index');
    }

    public function edit(Stack $stack): Response
    {
        return Inertia::render('Admin/Stacks/Form', [
            'stack' => $stack,
        ]);
    }

    public function update(UpdateStackRequest $request, Stack $stack): RedirectResponse
    {
        $this->stackService->update($stack, $request->validated());

        return redirect()->route('admin.stacks.index');
    }

    public function destroy(Stack $stack): RedirectResponse
    {
        $this->stackService->destroy($stack);

        return redirect()->route('admin.stacks.index');
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer', 'exists:stacks,id'],
        ]);

        $this->stackService->reorder($validated['ordered_ids']);

        return response()->json(['status' => 'ok']);
    }
}
