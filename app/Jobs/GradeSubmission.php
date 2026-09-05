<?php

namespace App\Jobs;

use App\Enums\SubmissionStatus;
use App\Models\Submission;
use App\Services\AssignmentGrader;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * Проверка работы вне запроса.
 *
 * Одна сборка на площадке занимает секунды, а на Go — около двадцати, поэтому
 * работа с пятью кейсами не укладывается ни в какой разумный таймаут страницы.
 */
class GradeSubmission implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 300;

    public function __construct(public Submission $submission) {}

    public function handle(AssignmentGrader $grader): void
    {
        $grader->fill($this->submission);
    }

    /**
     * Работа не должна навсегда остаться в статусе «проверяется»: страница
     * ученика опрашивает результат и иначе крутила бы ожидание бесконечно.
     */
    public function failed(?Throwable $e): void
    {
        $this->submission->update([
            'status' => SubmissionStatus::Error,
            'output' => __('Проверка не удалась, попробуйте отправить ещё раз'),
        ]);
    }
}
