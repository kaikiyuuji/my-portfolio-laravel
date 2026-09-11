<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateResumeRequest;
use App\Services\ResumeService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ResumeController extends Controller
{
    public function __construct(private ResumeService $resumeService) {}

    public function edit(): Response
    {
        return Inertia::render('Admin/Resume/Edit', ['settings' => $this->resumeService->settings()]);
    }

    public function update(UpdateResumeRequest $request): RedirectResponse
    {
        $this->resumeService->updateSettings($request->validated());

        return redirect()->route('admin.resume.edit')->with('success', 'Dados do currículo atualizados.');
    }
}
