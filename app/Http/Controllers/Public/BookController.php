<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ListBooksRequest;
use App\Services\BookService;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    public function __construct(
        private BookService $bookService
    ) {}

    public function index(ListBooksRequest $request): Response
    {
        $filters = $request->filters();

        return Inertia::render('Public/Books/Index', [
            'books' => $this->bookService->published($filters),
            'filters' => $filters,
            'counts' => $this->bookService->publishedCounts(),
        ]);
    }
}
