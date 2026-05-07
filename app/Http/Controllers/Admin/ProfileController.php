<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProfileUpdateRequest;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {}

    /**
     * Display the portfolio profile edit form.
     *
     * NOTE: This manages the PORTFOLIO Profile model (public-facing data),
     * not the admin User account.
     */
    public function edit(): Response
    {
        return Inertia::render('Admin/Profile/Edit', [
            'profile' => $this->profileService->get(),
        ]);
    }

    /**
     * Update the portfolio profile.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Handle avatar upload separately
        if ($request->hasFile('avatar')) {
            $this->profileService->updateAvatar($request->file('avatar'));
        }

        // Update profile fields (excluding the avatar file)
        $this->profileService->update(
            collect($validated)->except('avatar')->toArray()
        );

        return redirect()->route('admin.profile.edit');
    }
}
