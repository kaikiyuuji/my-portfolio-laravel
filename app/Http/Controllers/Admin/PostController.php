<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Posts/Index', [
            'posts' => $this->postService->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Posts/Form');
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $data = collect($validated)->except('image')->toArray();

        $post = $this->postService->store($data);

        if ($request->hasFile('image')) {
            $this->postService->updateImage($post, $request->file('image'));
        }

        return redirect()->route('admin.posts.index');
    }

    public function edit(Post $post): Response
    {
        return Inertia::render('Admin/Posts/Form', [
            'post' => $post,
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $validated = $request->validated();

        $data = collect($validated)->except('image')->toArray();

        $this->postService->update($post, $data);

        if ($request->hasFile('image')) {
            $this->postService->updateImage($post->fresh(), $request->file('image'));
        }

        return redirect()->route('admin.posts.index');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->postService->destroy($post);

        return redirect()->route('admin.posts.index');
    }
}
