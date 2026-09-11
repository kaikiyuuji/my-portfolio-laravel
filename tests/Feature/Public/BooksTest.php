<?php

namespace Tests\Feature\Public;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BooksTest extends TestCase
{
    use RefreshDatabase;

    public function test_library_is_public_and_supports_an_empty_collection(): void
    {
        $this->get(route('books.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/Books/Index')
            ->has('books.data', 0)
            ->where('filters', ['search' => '', 'category' => ''])
            ->where('counts', ['total' => 0, 'academic' => 0, 'personal' => 0])
        );
    }

    public function test_library_only_exposes_published_books_in_stable_title_order(): void
    {
        Book::factory()->create(['title' => 'Zebra', 'category' => 'academic']);
        $first = Book::factory()->create(['title' => 'Alpha', 'category' => 'personal']);
        $second = Book::factory()->create(['title' => 'Alpha', 'category' => 'personal']);
        Book::factory()->unpublished()->create(['title' => 'Hidden', 'category' => 'academic']);

        $this->get(route('books.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('books.data', 3)
            ->where('books.data.0.id', $first->id)
            ->where('books.data.1.id', $second->id)
            ->where('books.data.2.title', 'Zebra')
            ->where('counts', ['total' => 3, 'academic' => 1, 'personal' => 2])
        );
    }

    public function test_search_matches_title_or_author_within_category_and_counts_ignore_filters(): void
    {
        $titleMatch = Book::factory()->create([
            'title' => 'Software Design', 'author' => 'A. Writer', 'category' => 'academic',
        ]);
        $authorMatch = Book::factory()->create([
            'title' => 'Architecture', 'author' => 'Software Author', 'category' => 'academic',
        ]);
        Book::factory()->create(['title' => 'Software Fiction', 'category' => 'personal']);
        Book::factory()->create(['title' => 'Mathematics', 'author' => 'B. Writer', 'category' => 'academic']);
        Book::factory()->unpublished()->create(['title' => 'Software Draft', 'category' => 'academic']);

        $this->get(route('books.index', ['search' => '  software  ', 'category' => 'academic']))
            ->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('books.data', 2)
            ->where('books.data.0.id', $authorMatch->id)
            ->where('books.data.1.id', $titleMatch->id)
            ->where('filters', ['search' => 'software', 'category' => 'academic'])
            ->where('counts', ['total' => 4, 'academic' => 3, 'personal' => 1])
            );
    }

    public function test_pagination_preserves_filters_and_has_no_overlap_between_pages(): void
    {
        Book::factory()->count(25)->create(['title' => 'Study', 'category' => 'academic']);
        Book::factory()->count(2)->create(['title' => 'Novel', 'category' => 'personal']);

        $this->get(route('books.index', ['search' => 'Study', 'category' => 'academic']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('books.data', 24)
                ->where('books.total', 25)
                ->where('books.last_page', 2)
                ->where('books.data.0.id', 1)
                ->where('books.data.23.id', 24)
                ->where('books.next_page_url', function (string $url) {
                    parse_str(parse_url($url, PHP_URL_QUERY), $query);

                    return $query === ['search' => 'Study', 'category' => 'academic', 'page' => '2'];
                })
            );

        $this->get(route('books.index', ['search' => 'Study', 'category' => 'academic', 'page' => 2]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('books.data', 1)
                ->where('books.data.0.id', 25)
                ->where('counts.total', 27)
            );
    }

    public function test_zero_is_treated_as_a_search_term(): void
    {
        Book::factory()->create(['title' => '2001: A Space Odyssey', 'author' => 'Arthur C. Clarke']);
        Book::factory()->create(['title' => 'Dune', 'author' => 'Frank Herbert']);

        $this->get(route('books.index', ['search' => '0']))->assertInertia(fn (Assert $page) => $page
            ->where('filters.search', '0')
            ->has('books.data', 1)
            ->where('books.data.0.title', '2001: A Space Odyssey')
        );
    }

    public function test_invalid_filters_are_normalized_and_search_length_is_bounded(): void
    {
        Book::factory()->create();

        $this->get('/livros?category=unknown&search[]=bad')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('filters', ['search' => '', 'category' => ''])
            ->has('books.data', 1)
        );
        $this->get('/livros?category[]=academic')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('filters.category', '')
        );
        $this->get(route('books.index', ['search' => str_repeat('á', 200)]))
            ->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('filters.search', str_repeat('á', 120))
            ->has('books.data', 0)
            ->where('counts.total', 1)
            );
    }
}
