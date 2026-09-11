<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class BookService
{
    public function __construct(
        private ImageUploadService $imageUploadService
    ) {}

    public function all(int $perPage = 20): LengthAwarePaginator
    {
        return Book::orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function published(array $filters, int $perPage = 24): LengthAwarePaginator
    {
        return Book::published()
            ->when($filters['category'] ?? '', fn (Builder $query, string $category) => $query->where('category', $category))
            ->when(($filters['search'] ?? '') !== '', function (Builder $query) use ($filters) {
                $search = $filters['search'];
                $query->where(function (Builder $query) use ($search) {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('author', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('title')
            ->orderBy('id')
            ->paginate($perPage)
            ->appends(array_filter($filters, fn ($value) => $value !== ''));
    }

    public function publishedCounts(): array
    {
        $counts = Book::published()
            ->selectRaw('category, COUNT(*) as aggregate')
            ->groupBy('category')
            ->pluck('aggregate', 'category');

        $academic = (int) ($counts['academic'] ?? 0);
        $personal = (int) ($counts['personal'] ?? 0);

        return ['total' => $academic + $personal, 'academic' => $academic, 'personal' => $personal];
    }

    public function store(array $data, ?UploadedFile $cover = null): Book
    {
        $data = Arr::except($data, ['cover', 'remove_cover', 'cover_path']);
        $newPath = $cover ? $this->storeCover($cover) : null;

        try {
            return DB::transaction(fn () => Book::create([...$data, 'cover_path' => $newPath]));
        } catch (Throwable $exception) {
            $this->deleteCover($newPath);

            throw $exception;
        }
    }

    public function update(Book $book, array $data, ?UploadedFile $cover = null): Book
    {
        $removeCover = (bool) ($data['remove_cover'] ?? false);
        $data = Arr::except($data, ['cover', 'remove_cover', 'cover_path']);
        $oldPath = $book->cover_path;
        $newPath = $cover ? $this->storeCover($cover) : null;

        if ($newPath || $removeCover) {
            $data['cover_path'] = $newPath;
        }

        try {
            DB::transaction(fn () => $book->update($data));
        } catch (Throwable $exception) {
            $this->deleteCover($newPath);

            throw $exception;
        }

        // The old cover remains available until the database points to its replacement.
        if ($newPath || $removeCover) {
            $this->deleteCover($oldPath);
        }

        return $book->fresh();
    }

    public function destroy(Book $book): void
    {
        $oldPath = $book->cover_path;

        DB::transaction(fn () => $book->delete());
        $this->deleteCover($oldPath);
    }

    private function storeCover(UploadedFile $cover): string
    {
        $path = $this->imageUploadService->store($cover, 'books');

        if (! $path) {
            throw new RuntimeException('Não foi possível salvar a capa do livro.');
        }

        return $path;
    }

    private function deleteCover(?string $path): void
    {
        if (! $path) {
            return;
        }

        try {
            $this->imageUploadService->delete($path);
        } catch (Throwable $exception) {
            // A failed cleanup must not undo a successful save or hide the original failure.
            report($exception);
        }
    }
}
