<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Jobs\GradeSubmission;
use App\Models\Assignment;
use App\Models\Submission;
use App\Services\AssignmentGrader;
use App\Services\CodeRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function show(Request $request, Assignment $assignment): View
    {
        $this->authorise($request, $assignment);

        $student = $request->user();

        return view('learn.assignment', [
            'assignment' => $assignment->load(['lesson.subject.direction', 'tests']),
            // Ученику показываются только открытые кейсы: скрытые существуют
            // ровно затем, чтобы решение нельзя было подогнать под ответ.
            'visibleTests' => $assignment->tests->where('is_hidden', false),
            'hiddenCount' => $assignment->tests->where('is_hidden', true)->count(),
            'submissions' => $student->submissions()
                ->where('assignment_id', $assignment->id)
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    /**
     * Прогон без сдачи: один запуск на входе, который ученик набрал сам.
     * Ничего не записывается и на оценку не влияет.
     */
    public function run(Request $request, Assignment $assignment, CodeRunner $runner): JsonResponse
    {
        $this->authorise($request, $assignment);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:'.CodeRunner::MAX_CODE_LENGTH],
            'stdin' => ['nullable', 'string', 'max:10000'],
        ]);

        return response()->json(
            $runner->run($data['code'], $assignment->language, $data['stdin'] ?? '')
        );
    }

    /**
     * Сдача уходит в очередь: пять кейсов на Go занимают около минуты, и
     * держать всё это время открытый запрос нельзя.
     */
    public function submit(Request $request, Assignment $assignment, AssignmentGrader $grader): RedirectResponse
    {
        $this->authorise($request, $assignment);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:'.CodeRunner::MAX_CODE_LENGTH],
        ]);

        $submission = $grader->accept($assignment, $request->user(), $data['code']);

        GradeSubmission::dispatch($submission);

        return redirect()
            ->route('learn.assignments.show', $assignment)
            ->with('submission', $submission->id);
    }

    /** Страница опрашивает этот адрес, пока работа не проверится. */
    public function status(Request $request, Submission $submission): JsonResponse
    {
        abort_unless($submission->user_id === $request->user()->id, 404);

        return response()->json([
            'status' => $submission->status->value,
            'label' => $submission->status->label(),
            'pending' => $submission->status->isPending(),
            'passed_count' => $submission->passed_count,
            'total_count' => $submission->total_count,
            'score' => $submission->score,
            'output' => $submission->output,
            'results' => $submission->test_results ?? [],
        ]);
    }

    private function authorise(Request $request, Assignment $assignment): void
    {
        abort_unless(
            $assignment->published && $request->user()->canSee($assignment->lesson),
            404
        );
    }
}
