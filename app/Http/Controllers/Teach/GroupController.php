<?php

namespace App\Http\Controllers\Teach;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(Request $request): View
    {
        return view('teach.groups', [
            'groups' => $this->visibleTo($request)
                ->with(['direction', 'teacher', 'schedules'])
                ->withCount('students')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(Request $request, Group $group): View
    {
        abort_unless($this->visibleTo($request)->whereKey($group->id)->exists(), 404);

        return view('teach.group', [
            'group' => $group->load(['direction.subjects.lessons', 'teacher', 'schedules', 'students']),
            'assessments' => $group->assessments()->orderByDesc('opens_at')->get(),
        ]);
    }

    /**
     * Преподаватель видит свои группы, администратор — все.
     */
    private function visibleTo(Request $request)
    {
        $query = Group::query()->where('is_archived', false);

        return $request->user()->isAdmin()
            ? $query
            : $query->where('teacher_id', $request->user()->id);
    }
}
