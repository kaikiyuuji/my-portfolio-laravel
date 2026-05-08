<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExperienceRequest;
use App\Http\Requests\Admin\UpdateExperienceRequest;
use App\Models\Experience;
use App\Services\ExperienceService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExperienceController extends Controller
{
    public function __construct(
        private ExperienceService $experienceService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Experiences/Index', [
            'experiences' => $this->experienceService->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Experiences/Form');
    }

    public function store(StoreExperienceRequest $request): RedirectResponse
    {
        $this->experienceService->store($request->validated());

        return redirect()->route('admin.experiences.index');
    }

    public function edit(Experience $experience): Response
    {
        return Inertia::render('Admin/Experiences/Form', [
            'experience' => $experience,
        ]);
    }

    public function update(UpdateExperienceRequest $request, Experience $experience): RedirectResponse
    {
        $this->experienceService->update($experience, $request->validated());

        return redirect()->route('admin.experiences.index');
    }

    public function destroy(Experience $experience): RedirectResponse
    {
        $this->experienceService->destroy($experience);

        return redirect()->route('admin.experiences.index');
    }
}
