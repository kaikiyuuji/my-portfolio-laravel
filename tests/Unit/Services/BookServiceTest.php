<?php

namespace Tests\Unit\Services;

use App\Models\Book;
use App\Services\BookService;
use App\Services\ImageUploadService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class BookServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['filesystems.default' => 'public']);
        Storage::fake('public');
    }

    public function test_failed_database_insert_cleans_up_new_upload(): void
    {
        try {
            app(BookService::class)->store([
                'title' => null, 'author' => 'Author', 'category' => 'personal',
            ], UploadedFile::fake()->image('cover.jpg'));
            $this->fail('The invalid database write should fail.');
        } catch (QueryException) {
            $this->assertDatabaseCount('books', 0);
            $this->assertCount(0, Storage::allFiles('books'));
        }
    }

    public function test_failed_database_update_preserves_old_cover_and_cleans_up_new_upload(): void
    {
        $oldPath = UploadedFile::fake()->image('old.jpg')->store('books', 'public');
        $book = Book::factory()->create(['title' => 'Original', 'cover_path' => $oldPath]);

        try {
            app(BookService::class)->update($book, ['title' => null], UploadedFile::fake()->image('new.jpg'));
            $this->fail('The invalid database write should fail.');
        } catch (QueryException) {
            $this->assertSame('Original', $book->fresh()->title);
            $this->assertSame($oldPath, $book->fresh()->cover_path);
            Storage::assertExists($oldPath);
            $this->assertSame([$oldPath], Storage::allFiles('books'));
        }
    }

    public function test_upload_failure_does_not_change_existing_book(): void
    {
        $oldPath = UploadedFile::fake()->image('old.jpg')->store('books', 'public');
        $book = Book::factory()->create(['title' => 'Original', 'cover_path' => $oldPath]);
        $upload = Mockery::mock(ImageUploadService::class);
        $upload->shouldReceive('store')->once()->andThrow(new RuntimeException('Storage unavailable'));
        $upload->shouldNotReceive('delete');

        try {
            (new BookService($upload))->update($book, ['title' => 'Changed'], UploadedFile::fake()->image('new.jpg'));
            $this->fail('The upload should fail.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Storage unavailable', $exception->getMessage());
            $this->assertSame('Original', $book->fresh()->title);
            $this->assertSame($oldPath, $book->fresh()->cover_path);
            Storage::assertExists($oldPath);
        }
    }

    public function test_silent_upload_failure_does_not_create_book(): void
    {
        $upload = Mockery::mock(ImageUploadService::class);
        $upload->shouldReceive('store')->once()->andReturn('');

        try {
            (new BookService($upload))->store([
                'title' => 'Example', 'author' => 'Author', 'category' => 'personal',
            ], UploadedFile::fake()->image('cover.jpg'));
            $this->fail('An empty stored path should be treated as a failed upload.');
        } catch (RuntimeException) {
            $this->assertDatabaseCount('books', 0);
        }
    }

    public function test_failed_deletion_does_not_remove_cover_of_existing_book(): void
    {
        $path = UploadedFile::fake()->image('cover.jpg')->store('books', 'public');
        $book = Book::factory()->create(['cover_path' => $path]);
        $event = 'eloquent.deleting: '.Book::class;
        Event::listen($event, fn () => throw new RuntimeException('Delete failed'));

        try {
            app(BookService::class)->destroy($book);
            $this->fail('The database deletion should fail.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Delete failed', $exception->getMessage());
            $this->assertDatabaseHas('books', ['id' => $book->id]);
            Storage::assertExists($path);
        } finally {
            Event::forget($event);
        }
    }

    public function test_new_cover_wins_when_replacement_and_removal_are_both_requested(): void
    {
        $path = UploadedFile::fake()->image('old.jpg')->store('books', 'public');
        $book = Book::factory()->create(['cover_path' => $path]);

        $updated = app(BookService::class)->update($book, ['remove_cover' => true], UploadedFile::fake()->image('new.png'));

        $this->assertNotNull($updated->cover_path);
        $this->assertNotSame($path, $updated->cover_path);
        Storage::assertMissing($path);
        Storage::assertExists($updated->cover_path);
    }
}
