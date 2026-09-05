<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use Illuminate\View\View;

class DirectionController extends Controller
{
    public function index(): View
    {
        return view('directions.index', [
            'directions' => Direction::query()
                ->where('published', true)
                ->withCount(['subjects', 'groups'])
                ->orderBy('position')
                ->get(),
        ]);
    }

    public function show(Direction $direction): View
    {
        abort_unless($direction->published, 404);

        return view('directions.show', [
            'direction' => $direction->load(['teacher', 'subjects.lessons', 'groups.schedules']),
        ]);
    }
}
