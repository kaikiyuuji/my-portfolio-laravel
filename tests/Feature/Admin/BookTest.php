<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['filesystems.default' => 'public']);
        Storage::fake('public');
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'title' => 'Engenharia de Software',
            'author' => 'Ian Sommerville',
            'category' => 'academic',
            'description' => 'Uma referência para meus estudos.',
            'is_published' => true,
        ], $overrides);
    }

    public function test_every_admin_route_requires_authentication(): void
    {
        $book = Book::factory()->create();

        foreach ([
            ['get', '/admin/books'],
            ['get', '/admin/books/create'],
            ['get', "/admin/books/{$book->id}/edit"],
            ['post', '/admin/books'],
            ['put', "/admin/books/{$book->id}"],
            ['delete', "/admin/books/{$book->id}"],
        ] as [$method, $url]) {
            $this->{$method}($url)->assertRedirect('/login');
        }

        $this->assertDatabaseCount('books', 1);
    }

    public function test_admin_can_view_paginated_books_including_drafts_and_forms(): void
    {
        Book::factory()->count(20)->create();
        $draft = Book::factory()->unpublished()->create();
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.books.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Books/Index')
            ->has('books.data', 20)
            ->where('books.total', 21)
            ->where('books.data.0.id', $draft->id)
            ->where('books.data.0.is_published', false)
        );
        $this->get(route('admin.books.create'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Books/Form')
        );
        $this->get(route('admin.books.edit', $draft))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Books/Form')->where('book.id', $draft->id)
        );
    }

    public function test_admin_can_create_a_book_with_a_cover_and_original_language_text(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.books.store'), $this->payload([
                'title' => '百年の孤独 — Cem anos de solidão',
                'author' => 'Gabriel García Márquez',
                'category' => 'personal',
                'cover' => UploadedFile::fake()->image('capa.jpg', 600, 900),
            ]))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success')
            ->assertRedirect(route('admin.books.index'));

        $book = Book::sole();
        $this->assertSame('百年の孤独 — Cem anos de solidão', $book->title);
        $this->assertSame('Gabriel García Márquez', $book->author);
        $this->assertSame('personal', $book->category);
        $this->assertTrue($book->is_published);
        Storage::disk('public')->assertExists($book->cover_path);
        $this->assertSame(Storage::url($book->cover_path), $book->cover_url);

        $this->get(route('admin.books.index'))->assertInertia(fn (Assert $page) => $page
            ->where('flash.success', 'Livro adicionado à biblioteca.')
        );
    }

    public function test_cover_and_description_are_optional_and_publication_defaults_to_true(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.books.store'), [
            'title' => 'Duna', 'author' => 'Frank Herbert', 'category' => 'personal',
            'cover_path' => 'books/untrusted.jpg',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.books.index'));

        $book = Book::sole();
        $this->assertTrue($book->is_published);
        $this->assertNull($book->description);
        $this->assertNull($book->cover_path);
        $this->assertNull($book->cover_url);
    }

    public function test_invalid_required_fields_category_lengths_and_boolean_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.books.store'), $this->payload([
            'title' => '', 'author' => '', 'category' => 'other',
        ]))->assertSessionHasErrors(['title', 'author', 'category']);

        $this->post(route('admin.books.store'), $this->payload([
            'title' => str_repeat('a', 256),
            'author' => str_repeat('a', 256),
            'description' => str_repeat('a', 5001),
            'is_published' => 'invalid',
        ]))->assertSessionHasErrors(['title', 'author', 'description', 'is_published']);

        $this->assertDatabaseCount('books', 0);
    }

    #[DataProvider('invalidCovers')]
    public function test_invalid_cover_files_are_rejected(string $filename, string $mime, int $kilobytes): void
    {
        $cover = $filename === 'large.jpg'
            ? UploadedFile::fake()->image($filename)->size($kilobytes)
            : UploadedFile::fake()->create($filename, $kilobytes, $mime);

        $this->actingAs(User::factory()->create())->post(route('admin.books.store'), $this->payload([
            'cover' => $cover,
        ]))->assertSessionHasErrors('cover');

        $this->assertDatabaseCount('books', 0);
        $this->assertCount(0, Storage::allFiles('books'));
    }

    public static function invalidCovers(): array
    {
        return [
            'svg' => ['cover.svg', 'image/svg+xml', 1],
            'gif' => ['cover.gif', 'image/gif', 1],
            'document' => ['cover.pdf', 'application/pdf', 1],
            'oversize' => ['large.jpg', 'image/jpeg', 5121],
        ];
    }

    public function test_admin_can_replace_and_remove_cover_and_publish_state(): void
    {
        $oldPath = UploadedFile::fake()->image('old.jpg')->store('books', 'public');
        $book = Book::factory()->create(['cover_path' => $oldPath]);
        $this->actingAs(User::factory()->create());

        // A multipart POST with method override is how the Inertia edit form sends files.
        $this->post(route('admin.books.update', $book), $this->payload([
            '_method' => 'put',
            'title' => 'Título atualizado',
            'is_published' => '0',
            'cover' => UploadedFile::fake()->image('new.png'),
        ]))->assertSessionHasNoErrors()->assertRedirect(route('admin.books.index'));

        $book->refresh();
        $newPath = $book->cover_path;
        $this->assertSame('Título atualizado', $book->title);
        $this->assertFalse($book->is_published);
        Storage::assertMissing($oldPath);
        Storage::assertExists($newPath);

        $this->put(route('admin.books.update', $book), $this->payload([
            'remove_cover' => true,
        ]))->assertSessionHasNoErrors()->assertRedirect(route('admin.books.index'));

        $book->refresh();
        $this->assertNull($book->cover_path);
        $this->assertTrue($book->is_published);
        Storage::assertMissing($newPath);
    }

    public function test_editing_text_preserves_cover_and_invalid_update_preserves_book(): void
    {
        $path = UploadedFile::fake()->image('cover.jpg')->store('books', 'public');
        $book = Book::factory()->create(['cover_path' => $path]);
        $this->actingAs(User::factory()->create());

        $this->put(route('admin.books.update', $book), $this->payload())
            ->assertSessionHasNoErrors();
        $this->assertSame($path, $book->fresh()->cover_path);

        $this->put(route('admin.books.update', $book), $this->payload([
            'title' => '', 'remove_cover' => 'invalid',
        ]))->assertSessionHasErrors(['title', 'remove_cover']);

        $this->assertSame('Engenharia de Software', $book->fresh()->title);
        Storage::assertExists($path);
    }

    public function test_deleting_book_removes_its_cover(): void
    {
        $path = UploadedFile::fake()->image('cover.webp')->store('books', 'public');
        $book = Book::factory()->create(['cover_path' => $path]);

        $this->actingAs(User::factory()->create())->delete(route('admin.books.destroy', $book))
            ->assertSessionHas('success')
            ->assertRedirect(route('admin.books.index'));

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        Storage::assertMissing($path);
    }

    public function test_unknown_books_return_not_found(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin/books/999/edit')->assertNotFound();
        $this->put('/admin/books/999', $this->payload())->assertNotFound();
        $this->delete('/admin/books/999')->assertNotFound();
    }

    public function test_purchase_link_is_saved_and_available_in_edit_and_public_library(): void
    {
        $url = 'https://shop.example.com/books/software?ref=portfolio&edition=2';
        $this->actingAs(User::factory()->create())
            ->post(route('admin.books.store'), $this->payload(['purchase_url' => $url]))
            ->assertSessionHasNoErrors();

        $book = Book::sole();
        $this->assertSame($url, $book->purchase_url);
        $this->get(route('admin.books.edit', $book))->assertInertia(fn (Assert $page) => $page
            ->where('book.purchase_url', $url)
        );
        $this->get(route('books.index'))->assertInertia(fn (Assert $page) => $page
            ->where('books.data.0.purchase_url', $url)
        );
    }

    public function test_purchase_link_can_be_replaced_preserved_or_cleared(): void
    {
        $book = Book::factory()->create(['purchase_url' => 'https://shop.example.com/old']);
        $url = 'http://shop.example.com/new';
        $this->actingAs(User::factory()->create());
        $this->post(route('admin.books.update', $book), $this->payload([
            '_method' => 'put', 'purchase_url' => $url,
        ]))->assertSessionHasNoErrors();
        $this->assertSame($url, $book->fresh()->purchase_url);

        $this->put(route('admin.books.update', $book), $this->payload())->assertSessionHasNoErrors();
        $this->assertSame($url, $book->fresh()->purchase_url);

        $this->put(route('admin.books.update', $book), $this->payload(['purchase_url' => '']))
            ->assertSessionHasNoErrors();
        $this->assertNull($book->fresh()->purchase_url);
    }

    #[DataProvider('invalidPurchaseLinks')]
    public function test_invalid_purchase_links_are_rejected_on_create_and_update(string $url): void
    {
        $this->actingAs(User::factory()->create());
        $this->post(route('admin.books.store'), $this->payload(['purchase_url' => $url]))
            ->assertSessionHasErrors('purchase_url');
        $this->assertDatabaseCount('books', 0);

        $book = Book::factory()->create(['purchase_url' => 'https://shop.example.com/valid']);
        $this->put(route('admin.books.update', $book), $this->payload(['purchase_url' => $url]))
            ->assertSessionHasErrors('purchase_url');
        $this->assertSame('https://shop.example.com/valid', $book->fresh()->purchase_url);
    }

    public static function invalidPurchaseLinks(): array
    {
        return [
            'javascript' => ['javascript:alert(1)'],
            'data' => ['data:text/html,<h1>test</h1>'],
            'ftp' => ['ftp://shop.example.com/book'],
            'relative' => ['/books/software'],
            'missing protocol' => ['shop.example.com/books/software'],
            'oversize' => ['https://shop.example.com/'.str_repeat('a', 2048)],
        ];
    }
}
