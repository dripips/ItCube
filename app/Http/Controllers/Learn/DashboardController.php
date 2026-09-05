<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Lesson;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user();
        $groups = $student->groups()->with(['direction', 'teacher', 'schedules'])->get();
        $directionIds = $groups->pluck('direction_id');

        $lessons = Lesson::query()
            ->whereHas('subject', fn ($q) => $q->whereIn('direction_id', $directionIds))
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['subject.direction', 'assignments', 'quizzes'])
            ->orderBy('position')
            ->get();

        $solved = $student->submissions()
            ->where('status', 'passed')
            ->pluck('assignment_id')
            ->unique();

        return view('learn.index', [
            'groups' => $groups,
            'lessons' => $lessons,
            'solvedAssignmentIds' => $solved,
            'openAssessments' => Assessment::query()
                ->whereIn('group_id', $groups->pluck('id'))
                ->where('published', true)
                ->orderBy('closes_at')
                ->get()
                ->filter(fn (Assessment $a): bool => $a->isOpen()),
        ]);
    }

    public function schedule(Request $request): View
    {
        $groups = $request->user()->groups()->with(['direction', 'teacher'])->get();

        return view('learn.schedule', [
            'groups' => $groups,
            'byDay' => Schedule::query()
                ->whereIn('group_id', $groups->pluck('id'))
                ->with('group.direction')
                ->orderBy('starts_at')
                ->get()
                ->groupBy('day_of_week'),
        ]);
    }
}
