<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Stack;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Projects/Index', [
            'projects' => $this->projectService->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Projects/Form', [
            'stacks' => Stack::orderBy('order')->get(),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $stackIds = $validated['stack_ids'] ?? [];

        $data = collect($validated)->except(['stack_ids', 'image'])->toArray();

        $project = $this->projectService->store($data, $stackIds);

        if ($request->hasFile('image')) {
            $this->projectService->updateImage($project, $request->file('image'));
        }

        return redirect()->route('admin.projects.index');
    }

    public function edit(Project $project): Response
    {
        return Inertia::render('Admin/Projects/Form', [
            'project' => $project->load('stacks'),
            'stacks' => Stack::orderBy('order')->get(),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();
        $stackIds = $validated['stack_ids'] ?? [];

        $data = collect($validated)->except(['stack_ids', 'image'])->toArray();

        $this->projectService->update($project, $data, $stackIds);

        if ($request->hasFile('image')) {
            $this->projectService->updateImage($project, $request->file('image'));
        }

        return redirect()->route('admin.projects.index');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->projectService->destroy($project);

        return redirect()->route('admin.projects.index');
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer', 'exists:projects,id'],
        ]);

        $this->projectService->reorder($validated['ordered_ids']);

        return response()->json(['status' => 'ok']);
    }
}
