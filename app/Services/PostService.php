<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PostService
{
    public function __construct(
        private ImageUploadService $imageUploadService
    ) {}

    public function all(): Collection
    {
        return Post::orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();
    }

    public function published(int $perPage = 9): LengthAwarePaginator
    {
        return Post::published()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        return Post::published()->where('slug', $slug)->first();
    }

    public function store(array $data): Post
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['title']['pt'] ?? '');
        $data['published_at'] = $this->resolvePublishedAt($data);

        return Post::create($data);
    }

    public function update(Post $post, array $data): Post
    {
        if (isset($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $data['title']['pt'] ?? '', $post->id);
        }
        $data['published_at'] = $this->resolvePublishedAt($data, $post);

        $post->update($data);

        return $post->fresh();
    }

    /**
     * Replace post cover image. Stores the new file before deleting the old one
     * to guarantee the post is never left pointing to a missing file.
     */
    public function updateImage(Post $post, UploadedFile $file): string
    {
        $oldPath = $post->image_path;
        $newPath = $this->imageUploadService->store($file, 'posts');
        $post->update(['image_path' => $newPath]);
        $this->imageUploadService->delete($oldPath);

        return $newPath;
    }

    public function destroy(Post $post): void
    {
        $this->imageUploadService->delete($post->image_path);
        $post->delete();
    }

    private function uniqueSlug(?string $candidate, string $fallback, ?int $ignoreId = null): string
    {
        $slug = Str::slug($candidate ?: $fallback) ?: 'post';
        $base = $slug;
        $suffix = 2;

        while (Post::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function resolvePublishedAt(array $data, ?Post $post = null): ?string
    {
        $isPublished = (bool) ($data['is_published'] ?? false);

        if (! $isPublished) {
            return null;
        }

        if (! empty($data['published_at'])) {
            return $data['published_at'];
        }

        return $post?->published_at?->toDateTimeString() ?? now()->toDateTimeString();
    }
}
