<?php

namespace App\Http\Controllers\Teach;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Group;
use App\Services\AssessmentSheet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function index(Request $request): View
    {
        return view('teach.assessments', [
            'assessments' => Assessment::query()
                ->whereIn('group_id', $this->visibleGroupIds($request))
                ->with(['group.direction', 'items'])
                ->withCount('attempts')
                ->orderByDesc('opens_at')
                ->get(),
        ]);
    }

    public function show(Request $request, Assessment $assessment, AssessmentSheet $sheet): View
    {
        abort_unless($this->visibleGroupIds($request)->contains($assessment->group_id), 404);

        return view('teach.assessment', [
            'assessment' => $assessment->load(['group.direction', 'items.itemable']),
            'sheet' => $sheet->build($assessment),
        ]);
    }

    private function visibleGroupIds(Request $request)
    {
        $query = Group::query();

        if (! $request->user()->isAdmin()) {
            $query->where('teacher_id', $request->user()->id);
        }

        return $query->pluck('id');
    }
}
