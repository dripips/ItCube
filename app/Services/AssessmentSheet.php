<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentItem;
use App\Models\Assignment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Ведомость по контрольной: строка на ученика, колонка на работу.
 *
 * Баллы берутся по лучшей попытке. Ученик, который решил задачу со второго
 * раза, решил её — снижать за число попыток контрольная не должна, иначе
 * выгоднее не пробовать.
 */
final class AssessmentSheet
{
    /**
     * @return array{items: Collection<int, AssessmentItem>, rows: Collection<int, array<string, mixed>>, max: int}
     */
    public function build(Assessment $assessment): array
    {
        $items = $assessment->items()->with('itemable')->get();
        $students = $assessment->group->students()->orderBy('last_name')->get();

        $quizIds = $items->where('itemable_type', Quiz::class)->pluck('itemable_id');
        $assignmentIds = $items->where('itemable_type', Assignment::class)->pluck('itemable_id');

        $bestQuiz = $this->bestQuizScores($quizIds, $students);
        $bestWork = $this->bestSubmissionScores($assignmentIds, $students);

        $max = (int) $items->sum('points');

        $rows = $students->map(function (User $student) use ($items, $bestQuiz, $bestWork, $max): array {
            $cells = [];
            $earned = 0;

            foreach ($items as $item) {
                $key = $student->id.':'.$item->itemable_id;

                [$score, $outOf] = $item->itemable_type === Quiz::class
                    ? [$bestQuiz[$key] ?? null, $item->itemable?->maxScore() ?: 0]
                    : [$bestWork[$key] ?? null, $item->itemable?->maxScore() ?: 0];

                // Балл работы приводится к весу, который ей назначил
                // преподаватель: тест на десять вопросов и задача на три кейса
                // не обязаны стоить по-разному именно так, как совпало.
                $weighted = ($score === null || $outOf === 0)
                    ? null
                    : (int) round($score / $outOf * $item->points);

                $earned += $weighted ?? 0;

                $cells[] = [
                    'item' => $item,
                    'raw' => $score,
                    'out_of' => $outOf,
                    'points' => $weighted,
                    'max_points' => $item->points,
                ];
            }

            return [
                'student' => $student,
                'cells' => $cells,
                'earned' => $earned,
                'share' => $max > 0 ? (int) round($earned / $max * 100) : 0,
            ];
        });

        return ['items' => $items, 'rows' => $rows, 'max' => $max];
    }

    /**
     * @return array<string, int>
     */
    private function bestQuizScores(Collection $quizIds, Collection $students): array
    {
        if ($quizIds->isEmpty()) {
            return [];
        }

        return QuizAttempt::query()
            ->whereIn('quiz_id', $quizIds)
            ->whereIn('user_id', $students->pluck('id'))
            ->whereNotNull('submitted_at')
            ->get()
            ->groupBy(fn ($a): string => $a->user_id.':'.$a->quiz_id)
            ->map(fn (Collection $attempts): int => (int) $attempts->max('score'))
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function bestSubmissionScores(Collection $assignmentIds, Collection $students): array
    {
        if ($assignmentIds->isEmpty()) {
            return [];
        }

        return Submission::query()
            ->whereIn('assignment_id', $assignmentIds)
            ->whereIn('user_id', $students->pluck('id'))
            ->get()
            ->groupBy(fn (Submission $s): string => $s->user_id.':'.$s->assignment_id)
            ->map(fn (Collection $subs): int => (int) $subs->max('score'))
            ->all();
    }
}
