<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('posts.index', [
            'posts' => Post::query()
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->latest('published_at')
                ->paginate(10),
        ]);
    }

    public function show(Post $post): View
    {
        abort_if($post->published_at === null || $post->published_at->isFuture(), 404);

        return view('posts.show', ['post' => $post->load('author')]);
    }
}
