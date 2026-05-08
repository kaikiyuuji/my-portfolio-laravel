<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSocialLinkRequest;
use App\Http\Requests\Admin\UpdateSocialLinkRequest;
use App\Models\SocialLink;
use App\Services\SocialLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SocialLinkController extends Controller
{
    public function __construct(
        private SocialLinkService $socialLinkService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/SocialLinks/Index', [
            'socialLinks' => $this->socialLinkService->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/SocialLinks/Form');
    }

    public function store(StoreSocialLinkRequest $request): RedirectResponse
    {
        $this->socialLinkService->store($request->validated());

        return redirect()->route('admin.social-links.index');
    }

    public function edit(SocialLink $socialLink): Response
    {
        return Inertia::render('Admin/SocialLinks/Form', [
            'socialLink' => $socialLink,
        ]);
    }

    public function update(UpdateSocialLinkRequest $request, SocialLink $socialLink): RedirectResponse
    {
        $this->socialLinkService->update($socialLink, $request->validated());

        return redirect()->route('admin.social-links.index');
    }

    public function destroy(SocialLink $socialLink): RedirectResponse
    {
        $this->socialLinkService->destroy($socialLink);

        return redirect()->route('admin.social-links.index');
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer', 'exists:social_links,id'],
        ]);

        $this->socialLinkService->reorder($validated['ordered_ids']);

        return response()->json(['status' => 'ok']);
    }
}
