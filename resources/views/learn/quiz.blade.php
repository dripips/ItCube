@extends('layouts.app')
@section('title', $quiz->title.' — ItCube')

@section('content')
    <x-page-header :title="$quiz->title"
                   :subtitle="$quiz->description"
                   :back="$quiz->lesson ? route('learn.lessons.show', $quiz->lesson) : route('learn.index')">
        {{ $quiz->lesson?->title ?? __('Моё обучение') }}
    </x-page-header>

    @if ($attempt && $attempt->isFinished())
        <div class="surface mb-6 p-6">
            <h2 class="font-semibold">{{ __('Результат') }}</h2>
            <p class="mt-2 text-3xl font-bold">{{ $attempt->score }}<span class="muted text-lg"> / {{ $attempt->max_score }}</span></p>
            <p class="mt-1 text-sm muted">
                {{ __('Сдано') }} {{ $attempt->submitted_at->translatedFormat('j F, H:i') }}
            </p>
        </div>

        <div class="space-y-3">
            @foreach ($quiz->questions as $question)
                @php($answer = $attempt->answers->firstWhere('question_id', $question->id))
                <div class="surface p-5">
                    <div class="flex items-start gap-3">
                        <span class="{{ $answer?->is_correct ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $answer?->is_correct ? '✓' : '✗' }}
                        </span>
                        <p class="font-medium">{{ $question->text }}</p>
                    </div>

                    <ul class="mt-3 space-y-1 pl-6 text-sm">
                        @foreach ($question->options as $option)
                            @php($chosen = in_array($option->id, (array) ($answer?->chosen_option_ids ?? []), true))
                            <li class="{{ $option->is_correct ? 'text-emerald-700 dark:text-emerald-300' : ($chosen ? 'text-rose-700 dark:text-rose-300' : 'muted') }}">
                                {{ $chosen ? '●' : '○' }} {{ $option->text }}
                            </li>
                        @endforeach
                    </ul>

                    @if ($question->explanation)
                        <p class="mt-3 border-t pt-3 text-sm muted" style="border-color: var(--line)">{{ $question->explanation }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        @if ($attemptsLeft > 0)
            <form method="POST" action="{{ route('learn.quizzes.start', $quiz) }}" class="mt-6">
                @csrf
                <button class="btn-ghost">{{ trans_choice('{1}Пройти ещё раз, осталась :count попытка|[2,4]Пройти ещё раз, осталось :count попытки|[5,*]Пройти ещё раз, осталось :count попыток', $attemptsLeft, ['count' => $attemptsLeft]) }}</button>
            </form>
        @endif

    @elseif ($attempt)
        <form method="POST" action="{{ route('learn.quizzes.finish', $attempt) }}"
              data-quiz
              @if ($quiz->time_limit_minutes)
                  data-deadline="{{ $attempt->started_at->addMinutes($quiz->time_limit_minutes)->toIso8601String() }}"
              @endif>
            @csrf

            @if ($quiz->time_limit_minutes)
                <div class="surface sticky top-20 z-30 mb-4 flex items-center gap-3 px-5 py-3">
                    <span class="text-sm muted">{{ __('Осталось') }}</span>
                    <span class="font-mono text-lg font-medium tabular-nums" data-timer>—</span>
                    <span class="ml-auto text-xs muted">{{ __('Когда время выйдет, ответы отправятся сами') }}</span>
                </div>
            @endif

            <div class="space-y-3">
                @foreach ($quiz->questions as $question)
                    <fieldset class="surface p-5">
                        <legend class="mb-3 font-medium">
                            {{ $loop->iteration }}. {{ $question->text }}
                            <span class="badge ml-2">{{ $question->type->label() }}</span>
                        </legend>

                        @if ($question->type === App\Enums\QuestionType::Text)
                            <input type="text" name="answers[{{ $question->id }}]" class="field" autocomplete="off">
                        @else
                            <div class="space-y-2">
                                @foreach ($question->options as $option)
                                    <label class="flex cursor-pointer items-center gap-2.5 text-sm">
                                        <input type="{{ $question->type === App\Enums\QuestionType::Multiple ? 'checkbox' : 'radio' }}"
                                               name="answers[{{ $question->id }}][]"
                                               value="{{ $option->id }}">
                                        {{ $option->text }}
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </fieldset>
                @endforeach
            </div>

            <button class="btn-primary mt-6">{{ __('Сдать ответы') }}</button>
        </form>

    @else
        <div class="surface p-6">
            <p class="text-sm muted">
                {{ trans_choice('{1}:count вопрос|[2,4]:count вопроса|[5,*]:count вопросов', $quiz->questions->count(), ['count' => $quiz->questions->count()]) }}@if ($quiz->time_limit_minutes), {{ $quiz->time_limit_minutes }} {{ __('мин') }}@endif
            </p>
            <form method="POST" action="{{ route('learn.quizzes.start', $quiz) }}" class="mt-4">
                @csrf
                <button class="btn-primary" @disabled($attemptsLeft < 1)>{{ __('Начать') }}</button>
            </form>
            @if ($attemptsLeft < 1)
                <p class="mt-2 text-sm muted">{{ __('Попытки закончились') }}</p>
            @endif
        </div>
    @endif
@endsection
