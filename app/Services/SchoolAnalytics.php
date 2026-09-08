<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\Role;
use App\Enums\SubmissionStatus;
use App\Models\Attendance;
use App\Models\Group;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Сводка по учебному центру.
 *
 * Панель отвечает на вопросы, а не показывает счётчики: кто учится, как ходят,
 * что не получается. Счётчик «всего сдач 214» не говорит ни о чём, а список
 * задач, отсортированный по доле решивших, говорит, какую тему надо переобъяснить.
 *
 * Считается запросами с группировкой, а не перебором моделей: на выборке в
 * несколько тысяч отметок разница между тем и другим уже заметна глазом.
 */
final class SchoolAnalytics
{
    /**
     * @return array<string, int>
     */
    public function headcount(): array
    {
        $byRole = User::query()
            ->where('is_active', true)
            ->groupBy('role')
            ->select('role', DB::raw('count(*) as total'))
            ->pluck('total', 'role');

        return [
            'students' => (int) ($byRole[Role::Student->value] ?? 0),
            'teachers' => (int) ($byRole[Role::Teacher->value] ?? 0),
            'guardians' => (int) ($byRole[Role::Guardian->value] ?? 0),
            'groups' => Group::where('is_archived', false)->count(),
        ];
    }

    /**
     * Доля посещений по каждой группе.
     *
     * Пропуск по уважительной посещаемость не штрафует: иначе болезнь
     * выглядит в отчёте так же, как прогул.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function attendanceByGroup(): Collection
    {
        $excused = collect(AttendanceStatus::cases())
            ->filter(fn (AttendanceStatus $s): bool => $s->countsAsAttended())
            ->map(fn (AttendanceStatus $s): string => $s->value)
            ->all();

        return Attendance::query()
            ->join('groups', 'groups.id', '=', 'attendances.group_id')
            ->where('groups.is_archived', false)
            ->groupBy('groups.id', 'groups.name')
            ->select([
                'groups.id',
                'groups.name',
                DB::raw('count(*) as total'),
                DB::raw('count(*) filter (where attendances.status in ('
                    .implode(',', array_fill(0, count($excused), '?')).')) as attended'),
            ])
            ->addBinding($excused, 'select')
            ->get()
            ->map(fn ($row): array => [
                'group' => $row->name,
                'total' => (int) $row->total,
                'attended' => (int) $row->attended,
                'share' => $row->total > 0 ? (int) round($row->attended / $row->total * 100) : 0,
            ])
            ->sortBy('share')
            ->values();
    }

    /**
     * Посещаемость по неделям.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function attendanceTrend(int $weeks = 8): Collection
    {
        $since = Carbon::today()->startOfWeek()->subWeeks($weeks - 1);

        $excused = collect(AttendanceStatus::cases())
            ->filter(fn (AttendanceStatus $s): bool => $s->countsAsAttended())
            ->map(fn (AttendanceStatus $s): string => $s->value)
            ->all();

        $rows = Attendance::query()
            ->where('held_on', '>=', $since->toDateString())
            ->groupBy(DB::raw("date_trunc('week', held_on)"))
            ->select([
                DB::raw("date_trunc('week', held_on) as week"),
                DB::raw('count(*) as total'),
                DB::raw('count(*) filter (where status in ('
                    .implode(',', array_fill(0, count($excused), '?')).')) as attended'),
            ])
            ->addBinding($excused, 'select')
            ->orderBy('week')
            ->get()
            ->keyBy(fn ($row): string => Carbon::parse($row->week)->toDateString());

        // Недели без единого занятия всё равно должны быть на графике: провал
        // в расписании — это тоже факт, а не отсутствие данных.
        return collect(range(0, $weeks - 1))
            ->map(function (int $i) use ($since, $rows): array {
                $week = $since->copy()->addWeeks($i);
                $row = $rows->get($week->toDateString());
                $total = (int) ($row->total ?? 0);
                $attended = (int) ($row->attended ?? 0);

                return [
                    'week' => $week,
                    'total' => $total,
                    'attended' => $attended,
                    'share' => $total > 0 ? (int) round($attended / $total * 100) : null,
                ];
            });
    }

    /**
     * Задачи, отсортированные по доле решивших.
     *
     * Самая полезная таблица на панели: она показывает не активность, а тему,
     * которую надо переобъяснить.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function hardestAssignments(int $limit = 8): Collection
    {
        return Submission::query()
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->join('lessons', 'lessons.id', '=', 'assignments.lesson_id')
            ->groupBy('assignments.id', 'assignments.title', 'assignments.language', 'lessons.title')
            ->select([
                'assignments.id',
                'assignments.title',
                'assignments.language',
                'lessons.title as lesson',
                DB::raw('count(distinct submissions.user_id) as students'),
                DB::raw('count(*) as attempts'),
                DB::raw('count(distinct submissions.user_id) filter (where submissions.status = ?) as solved'),
            ])
            ->addBinding([SubmissionStatus::Passed->value], 'select')
            ->havingRaw('count(distinct submissions.user_id) > 0')
            ->get()
            ->map(fn ($row): array => [
                'id' => $row->id,
                'title' => $row->title,
                'lesson' => $row->lesson,
                'language' => $row->language,
                'students' => (int) $row->students,
                'attempts' => (int) $row->attempts,
                'solved' => (int) $row->solved,
                'share' => $row->students > 0 ? (int) round($row->solved / $row->students * 100) : 0,
                'attempts_per_student' => $row->students > 0 ? round($row->attempts / $row->students, 1) : 0.0,
            ])
            ->sortBy('share')
            ->take($limit)
            ->values();
    }

    /**
     * Сколько работ сдано на каждом языке.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function languageUsage(): Collection
    {
        return Submission::query()
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->groupBy('assignments.language')
            ->select('assignments.language', DB::raw('count(*) as total'))
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row): array => ['language' => $row->language, 'total' => (int) $row->total]);
    }

    /**
     * Сдачи по дням.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function submissionTrend(int $days = 14): Collection
    {
        $since = Carbon::today()->subDays($days - 1);

        $rows = Submission::query()
            ->where('created_at', '>=', $since->startOfDay())
            ->groupBy(DB::raw('date(created_at)'))
            ->select([DB::raw('date(created_at) as day'), DB::raw('count(*) as total')])
            ->get()
            ->keyBy(fn ($row): string => Carbon::parse($row->day)->toDateString());

        return collect(range(0, $days - 1))->map(function (int $i) use ($since, $rows): array {
            $day = $since->copy()->addDays($i);

            return ['day' => $day, 'total' => (int) ($rows->get($day->toDateString())->total ?? 0)];
        });
    }

    /**
     * Вопросы теста с самой низкой долей верных ответов.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function hardestQuestions(int $limit = 5): Collection
    {
        return DB::table('quiz_answers')
            ->join('questions', 'questions.id', '=', 'quiz_answers.question_id')
            ->join('quizzes', 'quizzes.id', '=', 'questions.quiz_id')
            ->groupBy('questions.id', 'questions.text', 'quizzes.title')
            ->select([
                'questions.text',
                'quizzes.title as quiz',
                DB::raw('count(*) as answers'),
                DB::raw('count(*) filter (where quiz_answers.is_correct) as correct'),
            ])
            ->get()
            ->map(fn ($row): array => [
                'text' => $row->text,
                'quiz' => $row->quiz,
                'answers' => (int) $row->answers,
                'correct' => (int) $row->correct,
                'share' => $row->answers > 0 ? (int) round($row->correct / $row->answers * 100) : 0,
            ])
            ->sortBy('share')
            ->take($limit)
            ->values();
    }
}
