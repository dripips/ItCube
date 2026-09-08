<?php

namespace App\Http\Controllers\Family;

use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Кабинет взрослого: только чтение и только про своих детей.
 *
 * Кода ребёнка родитель не видит намеренно. Ему нужен ответ на вопросы
 * «ходит ли» и «справляется ли», а разбор решения — дело преподавателя.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $children = $request->user()->children()->with(['groups.direction', 'groups.teacher'])->get();

        return view('family.index', [
            'children' => $children->map(fn (User $child): array => [
                'child' => $child,
                'attendance' => $this->attendanceShare($child),
                'solved' => $this->solved($child),
            ]),
        ]);
    }

    public function show(Request $request, User $child): View
    {
        abort_unless($request->user()->children()->whereKey($child->id)->exists(), 404);

        $groupIds = $child->groups()->pluck('groups.id');

        return view('family.child', [
            'child' => $child->load(['groups.direction', 'groups.teacher', 'groups.schedules']),
            'attendance' => $this->attendanceShare($child),
            'recentAttendance' => $child->attendances()
                ->with('group')
                ->orderByDesc('held_on')
                ->limit(12)
                ->get(),
            'submissions' => $child->submissions()
                ->with('assignment.lesson')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
            'solved' => $this->solved($child),
            'assessments' => Assessment::query()
                ->whereIn('group_id', $groupIds)
                ->where('published', true)
                ->with('group')
                ->orderByDesc('opens_at')
                ->limit(5)
                ->get(),
            'attempts' => $child->assessmentAttempts()->get()->keyBy('assessment_id'),
        ]);
    }

    /**
     * @return array{total: int, attended: int, share: int|null}
     */
    private function attendanceShare(User $child): array
    {
        $marks = Attendance::where('user_id', $child->id)->get();
        $attended = $marks->filter(fn (Attendance $m): bool => $m->status->countsAsAttended())->count();

        return [
            'total' => $marks->count(),
            'attended' => $attended,
            'share' => $marks->count() > 0 ? (int) round($attended / $marks->count() * 100) : null,
        ];
    }

    /**
     * @return array{solved: int, attempted: int}
     */
    private function solved(User $child): array
    {
        $subs = $child->submissions()->get();

        return [
            'solved' => $subs->where('status', SubmissionStatus::Passed)->pluck('assignment_id')->unique()->count(),
            'attempted' => $subs->pluck('assignment_id')->unique()->count(),
        ];
    }
}
