<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function __construct(
        private ImageUploadService $imageUploadService
    ) {}

    public function all(): Collection
    {
        return Project::with('stacks')->orderBy('order')->get();
    }

    public function store(array $data, array $stackIds): Project
    {
        return DB::transaction(function () use ($data, $stackIds) {
            $project = Project::create($data);
            $project->stacks()->sync($stackIds);

            return $project->load('stacks');
        });
    }

    public function update(Project $project, array $data, array $stackIds): Project
    {
        return DB::transaction(function () use ($project, $data, $stackIds) {
            $project->update($data);
            $project->stacks()->sync($stackIds);

            return $project->fresh('stacks');
        });
    }

    /**
     * Replace the project image. Stores the new file first, persists the new
     * path, then removes the old one — guarantees the project is never left
     * pointing at a missing image if the upload step fails.
     */
    public function updateImage(Project $project, UploadedFile $file): string
    {
        $oldPath = $project->image_path;

        $newPath = $this->imageUploadService->store($file, 'projects');
        $project->update(['image_path' => $newPath]);
        $this->imageUploadService->delete($oldPath);

        return $newPath;
    }

    public function destroy(Project $project): void
    {
        $this->imageUploadService->delete($project->image_path);
        $project->stacks()->detach();
        $project->delete();
    }

    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                Project::where('id', $id)->update(['order' => $index + 1]);
            }
        });
    }
}
