<?php

namespace App\Http\Controllers\Api;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Журнал в телефоне — та причина, по которой приложение вообще имеет смысл:
 * отмечать посещаемость удобнее, проходя по классу, а не сидя за ноутбуком.
 */
class TeachController extends Controller
{
    public function groups(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->visibleTo($request)
                ->with(['direction', 'schedules'])
                ->withCount('students')
                ->orderBy('name')
                ->get()
                ->map(fn (Group $group): array => [
                    'id' => $group->id,
                    'slug' => $group->slug,
                    'name' => $group->name,
                    'direction' => $group->direction->name,
                    'students' => $group->students_count,
                    'schedule' => $group->schedules->map(fn ($slot): array => [
                        'day' => $slot->day_of_week,
                        'starts_at' => substr($slot->starts_at, 0, 5),
                        'ends_at' => substr($slot->ends_at, 0, 5),
                    ])->values(),
                ])->values(),
        ]);
    }

    public function journal(Request $request): JsonResponse
    {
        $group = $this->visibleTo($request)->where('slug', $request->query('group'))->firstOrFail();
        $date = $request->date('date') ?? Carbon::today();

        $marks = Attendance::where('group_id', $group->id)
            ->whereDate('held_on', $date)
            ->get()
            ->keyBy('user_id');

        return response()->json([
            'group' => ['slug' => $group->slug, 'name' => $group->name],
            'date' => $date->toDateString(),
            'statuses' => collect(AttendanceStatus::cases())
                ->map(fn (AttendanceStatus $s): array => ['value' => $s->value, 'label' => $s->label()])
                ->values(),
            'students' => $group->students()->orderBy('last_name')->get()
                ->map(fn (User $student): array => [
                    'id' => $student->id,
                    'name' => $student->fullName(),
                    'status' => $marks->get($student->id)?->status->value,
                ])->values(),
        ]);
    }

    public function saveJournal(Request $request): JsonResponse
    {
        $data = $request->validate([
            'group' => ['required', 'exists:groups,slug'],
            'date' => ['required', 'date'],
            'marks' => ['array'],
            'marks.*' => [Rule::enum(AttendanceStatus::class)],
        ]);

        $group = $this->visibleTo($request)->where('slug', $data['group'])->firstOrFail();
        $enrolled = $group->students()->pluck('users.id');
        $saved = 0;

        foreach ($data['marks'] ?? [] as $userId => $status) {
            // Отметка ставится только тем, кто в группе: подменённое тело
            // запроса не должно заводить в журнал постороннего.
            if (! $enrolled->contains((int) $userId)) {
                continue;
            }

            Attendance::updateOrCreate(
                ['group_id' => $group->id, 'user_id' => (int) $userId, 'held_on' => $data['date']],
                ['status' => $status],
            );
            $saved++;
        }

        return response()->json(['saved' => $saved]);
    }

    private function visibleTo(Request $request)
    {
        $query = Group::query()->where('is_archived', false);

        return $request->user()->isAdmin() ? $query : $query->where('teacher_id', $request->user()->id);
    }
}
