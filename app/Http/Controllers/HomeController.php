<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use App\Models\Post;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'directions' => Direction::query()
                ->where('published', true)
                ->withCount(['subjects', 'groups'])
                ->orderBy('position')
                ->get(),
            'posts' => Post::query()
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->latest('published_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
