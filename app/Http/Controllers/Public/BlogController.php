<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PostService;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Public/Blog/Index', [
            'posts' => $this->postService->published(),
        ]);
    }

    public function show(string $slug): Response
    {
        $post = $this->postService->findPublishedBySlug($slug);

        if (! $post) {
            throw new NotFoundHttpException();
        }

        return Inertia::render('Public/Blog/Show', [
            'post' => $post,
            'related' => Post::published()
                ->where('id', '!=', $post->id)
                ->orderByDesc('published_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
