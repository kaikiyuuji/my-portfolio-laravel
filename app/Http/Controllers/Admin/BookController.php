<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBookRequest;
use App\Http\Requests\Admin\UpdateBookRequest;
use App\Models\Book;
use App\Services\BookService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    public function __construct(
        private BookService $bookService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Books/Index', [
            'books' => $this->bookService->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Books/Form');
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $this->bookService->store($request->validated(), $request->file('cover'));

        return redirect()->route('admin.books.index')->with('success', 'Livro adicionado à biblioteca.');
    }

    public function edit(Book $book): Response
    {
        return Inertia::render('Admin/Books/Form', ['book' => $book]);
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->bookService->update($book, $request->validated(), $request->file('cover'));

        return redirect()->route('admin.books.index')->with('success', 'Livro atualizado.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->bookService->destroy($book);

        return redirect()->route('admin.books.index')->with('success', 'Livro removido da biblioteca.');
    }
}
