<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\QuizScorer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function show(Request $request, Quiz $quiz): View
    {
        $this->authorise($request, $quiz);

        $student = $request->user();
        $attempt = $quiz->attempts()->where('user_id', $student->id)->latest()->first();

        return view('learn.quiz', [
            'quiz' => $quiz->load(['lesson.subject.direction', 'questions.options']),
            'attempt' => $attempt,
            'attemptsLeft' => max(0, $quiz->attempts_allowed - $quiz->attempts()->where('user_id', $student->id)->count()),
        ]);
    }

    public function start(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->authorise($request, $quiz);

        $student = $request->user();
        $used = $quiz->attempts()->where('user_id', $student->id)->count();

        abort_if($used >= $quiz->attempts_allowed, 403);

        $quiz->attempts()->create([
            'user_id' => $student->id,
            'started_at' => now(),
            'max_score' => $quiz->maxScore(),
        ]);

        return redirect()->route('learn.quizzes.show', $quiz);
    }

    public function finish(Request $request, QuizAttempt $attempt, QuizScorer $scorer): RedirectResponse
    {
        abort_unless($attempt->user_id === $request->user()->id, 404);
        abort_if($attempt->isFinished(), 403);

        $answers = $request->input('answers', []);

        $scorer->score($attempt, is_array($answers) ? $answers : []);

        return redirect()
            ->route('learn.quizzes.show', $attempt->quiz)
            ->with('status', __('Ответы приняты'));
    }

    private function authorise(Request $request, Quiz $quiz): void
    {
        abort_unless(
            $quiz->published && $quiz->lesson !== null && $request->user()->canSee($quiz->lesson),
            404
        );
    }
}
