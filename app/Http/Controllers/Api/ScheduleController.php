<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Support\Week;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Расписание того, кто спрашивает: ученику — его группы, преподавателю —
     * те, что он ведёт. Отдельных адресов на роль не нужно.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $groupIds = $user->isTeacher()
            ? $user->taughtGroups()->where('is_archived', false)->pluck('id')
            : $user->groups()->pluck('groups.id');

        $slots = Schedule::whereIn('group_id', $groupIds)
            ->with('group.direction')
            ->orderBy('day_of_week')
            ->orderBy('starts_at')
            ->get();

        return response()->json([
            'data' => $slots->map(fn (Schedule $slot): array => [
                'day' => $slot->day_of_week,
                'day_label' => Week::name($slot->day_of_week),
                'starts_at' => substr($slot->starts_at, 0, 5),
                'ends_at' => substr($slot->ends_at, 0, 5),
                'room' => $slot->room,
                'group' => $slot->group->name,
                'direction' => $slot->group->direction->name,
            ])->values(),
        ]);
    }
}
