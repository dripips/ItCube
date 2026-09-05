<?php

namespace App\Services;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuizAttempt;

/**
 * Подсчёт результата теста.
 *
 * Баллы считаются на сервере по ответам из базы, а не по тому, что прислала
 * форма: присланному можно дописать что угодно, включая собственную оценку.
 */
final class QuizScorer
{
    /**
     * @param  array<int, mixed>  $answers  идентификатор вопроса => ответ
     */
    public function score(QuizAttempt $attempt, array $answers): QuizAttempt
    {
        $questions = $attempt->quiz->questions()->with('options')->get();
        $total = 0;

        foreach ($questions as $question) {
            $given = $answers[$question->id] ?? null;
            $correct = $this->isCorrect($question, $given);
            $awarded = $correct ? $question->points : 0;
            $total += $awarded;

            $attempt->answers()->updateOrCreate(
                ['question_id' => $question->id],
                [
                    'chosen_option_ids' => $question->type === QuestionType::Text ? null : array_map(intval(...), (array) $given),
                    'text_answer' => $question->type === QuestionType::Text ? (string) $given : null,
                    'is_correct' => $correct,
                    'points_awarded' => $awarded,
                ],
            );
        }

        $attempt->update([
            'submitted_at' => now(),
            'score' => $total,
            'max_score' => (int) $questions->sum('points'),
        ]);

        return $attempt->refresh();
    }

    private function isCorrect(Question $question, mixed $given): bool
    {
        if ($given === null || $given === '' || $given === []) {
            return false;
        }

        if ($question->type === QuestionType::Text) {
            // Короткий ответ сверяется без учёта регистра и лишних пробелов:
            // «42 » и «42» — один и тот же ответ, а не разные.
            $expected = $question->options
                ->where('is_correct', true)
                ->map(fn ($option): string => $this->normalise($option->text));

            return $expected->contains($this->normalise((string) $given));
        }

        $chosen = collect((array) $given)->map(intval(...))->unique()->sort()->values();
        $correct = collect($question->correctOptionIds())->sort()->values();

        // Для вопроса с несколькими ответами частичное совпадение не
        // засчитывается: набор должен совпасть целиком.
        return $chosen->all() === $correct->all();
    }

    private function normalise(string $text): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $text) ?? $text));
    }
}
