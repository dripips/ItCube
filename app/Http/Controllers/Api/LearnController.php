<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\LessonResource;
use App\Http\Resources\SubmissionResource;
use App\Jobs\GradeSubmission;
use App\Models\Assignment;
use App\Models\Lesson;
use App\Models\Submission;
use App\Services\AssignmentGrader;
use App\Services\CodeRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LearnController extends Controller
{
    public function lessons(Request $request): JsonResponse
    {
        $directionIds = $request->user()->groups()->pluck('direction_id');

        $lessons = Lesson::query()
            ->whereHas('subject', fn ($q) => $q->whereIn('direction_id', $directionIds))
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['subject.direction', 'assignments', 'quizzes'])
            ->orderBy('position')
            ->get();

        return response()->json(['data' => LessonResource::collection($lessons)]);
    }

    public function lesson(Request $request, Lesson $lesson): JsonResponse
    {
        abort_unless($lesson->isPublished() && $request->user()->canSee($lesson), 404);

        return response()->json([
            'data' => new LessonResource($lesson->load(['subject.direction', 'assignments', 'quizzes'])),
        ]);
    }

    public function assignment(Request $request, Assignment $assignment): JsonResponse
    {
        $this->authorise($request, $assignment);

        return response()->json([
            'assignment' => new AssignmentResource($assignment->load(['lesson.subject.direction', 'tests'])),
            'submissions' => SubmissionResource::collection(
                $request->user()->submissions()
                    ->where('assignment_id', $assignment->id)
                    ->latest()
                    ->limit(10)
                    ->get()
            ),
        ]);
    }

    /** Прогон на своём вводе: ничего не записывается и на оценку не влияет. */
    public function run(Request $request, Assignment $assignment, CodeRunner $runner): JsonResponse
    {
        $this->authorise($request, $assignment);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:'.CodeRunner::MAX_CODE_LENGTH],
            'stdin' => ['nullable', 'string', 'max:10000'],
        ]);

        return response()->json($runner->run($data['code'], $assignment->language, $data['stdin'] ?? ''));
    }

    public function submit(Request $request, Assignment $assignment, AssignmentGrader $grader): JsonResponse
    {
        $this->authorise($request, $assignment);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:'.CodeRunner::MAX_CODE_LENGTH],
        ]);

        $submission = $grader->accept($assignment, $request->user(), $data['code']);

        GradeSubmission::dispatch($submission);

        return response()->json(['data' => new SubmissionResource($submission)], 202);
    }

    /** Приложение опрашивает этот адрес, пока работа не проверится. */
    public function submission(Request $request, Submission $submission): JsonResponse
    {
        abort_unless($submission->user_id === $request->user()->id, 404);

        return response()->json(['data' => new SubmissionResource($submission)]);
    }

    private function authorise(Request $request, Assignment $assignment): void
    {
        abort_unless($assignment->published && $request->user()->canSee($assignment->lesson), 404);
    }
}
