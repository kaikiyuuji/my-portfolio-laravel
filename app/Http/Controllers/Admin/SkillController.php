<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSkillRequest;
use App\Http\Requests\Admin\UpdateSkillRequest;
use App\Models\Skill;
use App\Services\SkillService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SkillController extends Controller
{
    public function __construct(
        private SkillService $skillService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Skills/Index', [
            'skills' => $this->skillService->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Skills/Form');
    }

    public function store(StoreSkillRequest $request): RedirectResponse
    {
        $this->skillService->store($request->validated());

        return redirect()->route('admin.skills.index')->with('success', 'Competência adicionada.');
    }

    public function edit(Skill $skill): Response
    {
        return Inertia::render('Admin/Skills/Form', ['skill' => $skill]);
    }

    public function update(UpdateSkillRequest $request, Skill $skill): RedirectResponse
    {
        $this->skillService->update($skill, $request->validated());

        return redirect()->route('admin.skills.index')->with('success', 'Competência atualizada.');
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $this->skillService->destroy($skill);

        return redirect()->route('admin.skills.index')->with('success', 'Competência removida.');
    }
}
