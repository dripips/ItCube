@extends('layouts.app')
@section('title', $assignment->title.' — ItCube')

@section('content')
    @php($last = $submissions->first())

    <x-page-header :title="$assignment->title"
                   :back="route('learn.lessons.show', $assignment->lesson)">{{ $assignment->lesson->title }}</x-page-header>

    <div class="grid gap-6 lg:grid-cols-5">
        <div class="space-y-6 lg:col-span-2">
            @if ($assignment->instructions)
                <div class="surface prose-lesson p-6">{!! nl2br(e($assignment->instructions)) !!}</div>
            @endif

            <div class="surface p-6">
                <h2 class="mb-3 flex items-center justify-between text-sm font-semibold">
                    {{ __('На чём проверяется') }}
                    <x-difficulty :level="$assignment->difficulty"/>
                </h2>

                <div class="space-y-3 text-sm">
                    @foreach ($visibleTests as $test)
                        <div class="rounded-lg p-3" style="background-color: var(--surface-sunken)">
                            <p class="mb-1.5 text-xs muted">{{ $test->name ?: __('Тест :n', ['n' => $loop->iteration]) }}</p>
                            @if (filled($test->stdin))
                                <p class="font-mono text-xs"><span class="muted">{{ __('вход') }}:</span> {{ $test->stdin }}</p>
                            @endif
                            <p class="font-mono text-xs"><span class="muted">{{ __('ответ') }}:</span> {{ $test->expected_output }}</p>
                        </div>
                    @endforeach

                    @if ($hiddenCount > 0)
                        <p class="text-xs muted">
                            {{ trans_choice('{1}И ещё :count скрытый набор данных: его условие не показывается, чтобы решение нельзя было подогнать под ответ.|[2,4]И ещё :count скрытых набора данных: их условия не показываются, чтобы решение нельзя было подогнать под ответ.|[5,*]И ещё :count скрытых наборов данных: их условия не показываются, чтобы решение нельзя было подогнать под ответ.', $hiddenCount, ['count' => $hiddenCount]) }}
                        </p>
                    @endif
                </div>
            </div>

            @if ($assignment->hint_list ?? false)
                <details class="surface p-6">
                    <summary class="cursor-pointer text-sm font-semibold">{{ __('Подсказки') }}</summary>
                    <ul class="mt-3 space-y-2 text-sm muted">
                        @foreach ($assignment->hints as $hint)
                            <li>{{ $hint }}</li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </div>

        <div class="space-y-4 lg:col-span-3"
             data-editor
             data-language="{{ $assignment->language }}"
             data-run-url="{{ route('learn.assignments.run', $assignment) }}"
             @if (session('submission')) data-poll-url="{{ route('learn.submissions.status', session('submission')) }}" @endif>

            <form method="POST" action="{{ route('learn.assignments.submit', $assignment) }}">
                @csrf

                <div class="surface overflow-hidden">
                    <div class="flex items-center gap-3 border-b px-4 py-2.5 text-xs" style="border-color: var(--line)">
                        <span class="font-mono font-medium">{{ $assignment->language }}</span>
                        <span class="muted">{{ __('Tab — отступ, Ctrl+Enter — запустить') }}</span>
                        <span class="ml-auto muted" data-char-count></span>
                    </div>

                    <div class="code flex">
                        <pre class="editor select-none py-4 pr-3 pl-4 text-right opacity-40" data-gutter aria-hidden="true">1</pre>
                        <textarea name="code" data-code
                                  class="editor w-full resize-y bg-transparent py-4 pr-4 outline-none"
                                  rows="16" spellcheck="false" autocomplete="off"
                                  required>{{ old('code', $last->code ?? $assignment->starter_code) }}</textarea>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button type="button" class="btn-ghost" data-run>{{ __('Запустить') }}</button>
                    <button type="submit" class="btn-primary" data-submit>{{ __('Сдать на проверку') }}</button>
                    <label class="ml-auto flex items-center gap-2 text-xs muted">
                        {{ __('Свой ввод') }}
                        <input type="text" data-stdin class="field w-40 py-1 font-mono text-xs" placeholder="{{ __('через ; — новая строка') }}">
                    </label>
                </div>
            </form>

            <div class="surface hidden p-5" data-run-output>
                <h2 class="mb-2 text-sm font-semibold">{{ __('Вывод программы') }}</h2>
                <pre class="code overflow-x-auto p-4 text-sm" data-run-text></pre>
            </div>

            <div class="surface p-5" data-result>
                @if ($last)
                    @include('learn.partials.submission', ['submission' => $last])
                @else
                    <p class="text-sm muted">{{ __('Работа ещё не сдавалась. Запустите код на своём вводе, а когда будете готовы — сдайте на проверку.') }}</p>
                @endif
            </div>

            @if ($submissions->count() > 1)
                <details class="surface p-5">
                    <summary class="cursor-pointer text-sm font-semibold">{{ __('Прошлые попытки') }}</summary>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($submissions->skip(1) as $old)
                            <li class="flex items-center gap-3">
                                <span class="muted">{{ __('Попытка :n', ['n' => $old->attempt_number]) }}</span>
                                <span class="{{ $old->passed() ? 'badge-pass' : 'badge-fail' }}">{{ $old->passed_count }}/{{ $old->total_count }}</span>
                                <time class="ml-auto text-xs muted">{{ $old->created_at->translatedFormat('j F, H:i') }}</time>
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </div>
    </div>
@endsection
