<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Кабинет взрослого в приложении: то же, что на сайте, и с тем же ограничением.
 * Кода ребёнка здесь нет, и в ответе API его тоже нет.
 */
class FamilyController extends Controller
{
    public function children(Request $request): JsonResponse
    {
        $children = $request->user()->children()->with('groups.direction')->get();

        return response()->json([
            'data' => $children->map(fn (User $child): array => $this->summary($child))->values(),
        ]);
    }

    public function child(Request $request, User $child): JsonResponse
    {
        abort_unless($request->user()->children()->whereKey($child->id)->exists(), 404);

        $child->load(['groups.direction', 'groups.teacher', 'groups.schedules']);

        return response()->json([
            'child' => $this->summary($child),
            'groups' => $child->groups->map(fn ($group): array => [
                'name' => $group->name,
                'direction' => $group->direction->name,
                'teacher' => $group->teacher?->fullName(),
                'schedule' => $group->schedules->map(fn ($slot): array => [
                    'day' => $slot->day_of_week,
                    'starts_at' => substr($slot->starts_at, 0, 5),
                    'ends_at' => substr($slot->ends_at, 0, 5),
                    'room' => $slot->room,
                ])->values(),
            ])->values(),
            'attendance' => $child->attendances()
                ->with('group')
                ->orderByDesc('held_on')
                ->limit(20)
                ->get()
                ->map(fn (Attendance $mark): array => [
                    'held_on' => $mark->held_on->toDateString(),
                    'group' => $mark->group->name,
                    'status' => $mark->status->value,
                    'status_label' => $mark->status->label(),
                    'attended' => $mark->status->countsAsAttended(),
                ])->values(),
            'submissions' => $child->submissions()
                ->with('assignment.lesson')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                // Ни code, ни test_results: родителю нужен счёт, а не разбор.
                ->map(fn ($submission): array => [
                    'assignment' => $submission->assignment->title,
                    'lesson' => $submission->assignment->lesson->title,
                    'passed' => $submission->passed(),
                    'passed_count' => $submission->passed_count,
                    'total_count' => $submission->total_count,
                    'created_at' => $submission->created_at?->toIso8601String(),
                ])->values(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(User $child): array
    {
        $marks = Attendance::where('user_id', $child->id)->get();
        $attended = $marks->filter(fn (Attendance $m): bool => $m->status->countsAsAttended())->count();
        $subs = $child->submissions()->get();

        return [
            'id' => $child->id,
            'name' => $child->fullName(),
            'groups' => $child->groups->map(fn ($g): string => $g->direction->name.' · '.$g->name)->values(),
            'attendance_share' => $marks->count() > 0 ? (int) round($attended / $marks->count() * 100) : null,
            'attendance_total' => $marks->count(),
            'attendance_attended' => $attended,
            'solved' => $subs->where('status', SubmissionStatus::Passed)->pluck('assignment_id')->unique()->count(),
            'attempted' => $subs->pluck('assignment_id')->unique()->count(),
        ];
    }
}
