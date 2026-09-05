<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $groupIds = $request->user()->groups()->pluck('groups.id');

        return view('learn.assessments', [
            'assessments' => Assessment::query()
                ->whereIn('group_id', $groupIds)
                ->where('published', true)
                ->with(['group.direction', 'items.itemable'])
                ->orderByDesc('opens_at')
                ->get(),
            'attempts' => $request->user()
                ->assessmentAttempts()
                ->get()
                ->keyBy('assessment_id'),
        ]);
    }

    public function show(Request $request, Assessment $assessment): View
    {
        $student = $request->user();

        abort_unless(
            $assessment->published && $student->groups()->whereKey($assessment->group_id)->exists(),
            404
        );

        return view('learn.assessment', [
            'assessment' => $assessment->load(['group.direction', 'items.itemable']),
            'attempt' => $assessment->attempts()->where('user_id', $student->id)->first(),
        ]);
    }
}
