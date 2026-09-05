<?php

namespace App\Services;

use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\AssignmentTest;
use App\Models\Submission;
use App\Models\User;

/**
 * Проверка работы ученика по набору тест-кейсов.
 *
 * Кейсы уходят на площадку одной пачкой и параллельно: каждому нужен свой
 * stdin, а сборка занимает секунды, и последовательный прогон превратил бы
 * контрольную в ожидание. Первый провал проверку не останавливает — ученик
 * должен увидеть, сколько кейсов не сошлось, а не только самый первый.
 */
final class AssignmentGrader
{
    public function __construct(private readonly CodeRunner $runner) {}

    public function grade(Assignment $assignment, User $student, string $code): Submission
    {
        $tests = $assignment->tests()->get();

        $results = $this->runner->runMany(
            $code,
            $assignment->language,
            $tests->map(fn (AssignmentTest $test): string => (string) $test->stdin)->values()->all(),
        );

        $rows = [];
        $passed = 0;
        $score = 0;
        $runtime = 0;
        $firstFailure = null;
        $blockedReason = null;

        foreach ($tests as $index => $test) {
            $result = $results[$index];
            $runtime += $result->runtimeMs;

            // Отказ до запуска относится к коду целиком, а не к отдельному
            // кейсу, поэтому и в карточке он показывается один раз.
            if ($result->status === RunResult::STATUS_BLOCKED) {
                $blockedReason = $result->output;

                break;
            }

            $ok = $result->success && $this->matches($result->output, $test->expected_output);

            if ($ok) {
                $passed++;
                $score += $test->points;
            } elseif ($firstFailure === null) {
                $firstFailure = $result->output;
            }

            $rows[] = $this->describe($test, $result, $ok);
        }

        $total = $tests->count();

        $status = match (true) {
            $blockedReason !== null => SubmissionStatus::Blocked,
            $total > 0 && $passed === $total => SubmissionStatus::Passed,
            $passed === 0 && $this->everyRunErrored($results) => SubmissionStatus::Error,
            default => SubmissionStatus::Failed,
        };

        return $student->submissions()->create([
            'assignment_id' => $assignment->id,
            'code' => $code,
            'status' => $status,
            'output' => $blockedReason ?? $firstFailure ?? __('Все тесты пройдены'),
            'test_results' => $rows,
            'passed_count' => $passed,
            'total_count' => $total,
            'score' => $blockedReason !== null ? 0 : $score,
            'attempt_number' => $assignment->submissions()->where('user_id', $student->id)->count() + 1,
            'runtime_ms' => $runtime,
        ]);
    }

    /**
     * Что показать ученику про один кейс.
     *
     * У скрытого кейса не раскрываются ни вход, ни ожидаемый ответ, ни то, что
     * выдала программа: иначе скрытый кейс перестаёт быть скрытым после первой
     * же попытки.
     *
     * @return array<string, mixed>
     */
    private function describe(AssignmentTest $test, RunResult $result, bool $ok): array
    {
        $row = [
            'id' => $test->id,
            'name' => $test->name ?: __('Тест :n', ['n' => $test->position + 1]),
            'hidden' => (bool) $test->is_hidden,
            'passed' => $ok,
            'points' => $ok ? $test->points : 0,
            'max_points' => $test->points,
        ];

        if ($test->is_hidden) {
            return $row;
        }

        return $row + [
            'stdin' => $test->stdin,
            'expected' => $test->expected_output,
            'actual' => $result->output,
            'runtime_ms' => $result->runtimeMs,
        ];
    }

    /**
     * Хвостовые пробелы и перевод строки в конце — не ошибка ученика, а разница
     * между print и println. Всё остальное, включая регистр, сверяется точно.
     */
    private function matches(string $actual, string $expected): bool
    {
        return $this->normalise($actual) === $this->normalise($expected);
    }

    private function normalise(string $text): string
    {
        $lines = preg_split('/\R/', trim($text)) ?: [];

        return implode("\n", array_map(rtrim(...), $lines));
    }

    /**
     * Ни один кейс не отработал: код не собрался или упал на всех входах.
     *
     * @param  list<RunResult>  $results
     */
    private function everyRunErrored(array $results): bool
    {
        if ($results === []) {
            return false;
        }

        foreach ($results as $result) {
            if ($result->success) {
                return false;
            }
        }

        return true;
    }
}
