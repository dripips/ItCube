<?php

namespace App\Http\Controllers\Teach;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function index(Request $request): View
    {
        $groups = $this->visibleTo($request)->with('direction')->orderBy('name')->get();

        $group = $request->filled('group')
            ? $groups->firstWhere('slug', $request->string('group')->toString())
            : $groups->first();

        $date = $request->date('date') ?? Carbon::today();

        return view('teach.journal', [
            'groups' => $groups,
            'group' => $group,
            'date' => $date,
            'students' => $group?->students()->orderBy('last_name')->get() ?? collect(),
            'marks' => $group
                ? Attendance::query()
                    ->where('group_id', $group->id)
                    ->whereDate('held_on', $date)
                    ->get()
                    ->keyBy('user_id')
                : collect(),
            'stats' => $group ? $this->attendanceShare($group) : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'group' => ['required', 'exists:groups,slug'],
            'date' => ['required', 'date'],
            'marks' => ['array'],
            'marks.*' => [Rule::enum(AttendanceStatus::class)],
        ]);

        $group = $this->visibleTo($request)->where('slug', $data['group'])->firstOrFail();
        $enrolled = $group->students()->pluck('users.id');

        foreach ($data['marks'] ?? [] as $userId => $status) {
            // Отметка ставится только тем, кто действительно в группе: иначе
            // подменённое поле формы завело бы в журнал чужого человека.
            if (! $enrolled->contains((int) $userId)) {
                continue;
            }

            Attendance::updateOrCreate(
                ['group_id' => $group->id, 'user_id' => (int) $userId, 'held_on' => $data['date']],
                ['status' => $status],
            );
        }

        return redirect()
            ->route('teach.journal.index', ['group' => $group->slug, 'date' => $data['date']])
            ->with('status', __('Журнал сохранён'));
    }

    /** Доля посещений по каждому ученику за всё время. */
    private function attendanceShare(Group $group)
    {
        return Attendance::query()
            ->where('group_id', $group->id)
            ->get()
            ->groupBy('user_id')
            ->map(function ($marks): array {
                $attended = $marks->filter(fn (Attendance $m): bool => $m->status->countsAsAttended())->count();

                return ['total' => $marks->count(), 'attended' => $attended];
            });
    }

    private function visibleTo(Request $request)
    {
        $query = Group::query()->where('is_archived', false);

        return $request->user()->isAdmin()
            ? $query
            : $query->where('teacher_id', $request->user()->id);
    }
}
