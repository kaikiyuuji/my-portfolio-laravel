<?php

namespace App\Services;

use App\Models\Profile;
use Illuminate\Http\UploadedFile;

class ProfileService
{
    public function __construct(
        private ImageUploadService $imageUploadService
    ) {}

    /**
     * Retrieve the singleton portfolio profile, creating one if none exists.
     */
    public function get(): Profile
    {
        return Profile::firstOrCreate([], [
            'name' => 'Your Name',
            'headline' => 'Your Title',
            'bio' => '',
            'email' => 'email@example.com',
        ]);
    }

    /**
     * Update the portfolio profile fields.
     */
    public function update(array $data): Profile
    {
        $profile = $this->get();
        $profile->update($data);

        return $profile;
    }

    /**
     * Upload a new avatar, deleting the previous one if it exists.
     * Returns the new storage path.
     */
    public function updateAvatar(UploadedFile $file): string
    {
        $profile = $this->get();

        // Delete old avatar if present
        $this->imageUploadService->delete($profile->avatar_path);

        // Store new avatar
        $path = $this->imageUploadService->store($file, 'avatars');

        // Update profile record
        $profile->update(['avatar_path' => $path]);

        return $path;
    }
}
