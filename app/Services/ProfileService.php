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
     * Upload a new avatar, deleting the previous one only after the new one
     * is safely stored and persisted in the database.
     */
    public function updateAvatar(UploadedFile $file): string
    {
        $profile = $this->get();
        $oldPath = $profile->avatar_path;

        // Store new avatar first — if this fails, the old avatar is preserved.
        $newPath = $this->imageUploadService->store($file, 'avatars');

        // Persist the new path in the database.
        $profile->update(['avatar_path' => $newPath]);

        // Only now it's safe to delete the old avatar.
        $this->imageUploadService->delete($oldPath);

        return $newPath;
    }
}
