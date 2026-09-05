<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function show(Request $request, Lesson $lesson): View
    {
        $student = $request->user();

        abort_unless($lesson->isPublished() && $student->canSee($lesson), 404);

        $lesson->load(['subject.direction', 'assignments.tests', 'quizzes']);

        return view('learn.lesson', [
            'lesson' => $lesson,
            'bestByAssignment' => $student->submissions()
                ->whereIn('assignment_id', $lesson->assignments->pluck('id'))
                ->orderByDesc('score')
                ->get()
                ->keyBy('assignment_id'),
        ]);
    }
}
