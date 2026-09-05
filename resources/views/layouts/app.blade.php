<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $theme ?? '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    {{-- Тема ставится до первой отрисовки, иначе тёмная страница успевает
         мигнуть белым при каждой загрузке. --}}
    <script>
        try {
            const saved = localStorage.getItem('theme');
            const dark = saved ? saved === 'dark' : matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', dark);
        } catch (e) {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
<header class="sticky top-0 z-40 border-b backdrop-blur" style="border-color: var(--line); background-color: color-mix(in oklab, var(--surface) 85%, transparent)">
    <div class="mx-auto flex h-16 max-w-6xl items-center gap-6 px-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-semibold">
            <x-cube class="h-7 w-7"/>
            <span>ItCube</span>
        </a>

        <nav class="hidden items-center gap-1 text-sm md:flex">
            @auth
                @if (auth()->user()->isTeacher())
                    <x-nav-link :href="route('teach.groups.index')" :active="request()->routeIs('teach.groups.*')">{{ __('Группы') }}</x-nav-link>
                    <x-nav-link :href="route('teach.journal.index')" :active="request()->routeIs('teach.journal.*')">{{ __('Журнал') }}</x-nav-link>
                    <x-nav-link :href="route('teach.assessments.index')" :active="request()->routeIs('teach.assessments.*')">{{ __('Контрольные') }}</x-nav-link>
                @else
                    <x-nav-link :href="route('learn.index')" :active="request()->routeIs('learn.index')">{{ __('Моё обучение') }}</x-nav-link>
                    <x-nav-link :href="route('learn.schedule')" :active="request()->routeIs('learn.schedule')">{{ __('Расписание') }}</x-nav-link>
                    <x-nav-link :href="route('learn.assessments')" :active="request()->routeIs('learn.assessments')">{{ __('Контрольные') }}</x-nav-link>
                @endif
            @else
                <x-nav-link :href="route('directions.index')" :active="request()->routeIs('directions.*')">{{ __('Направления') }}</x-nav-link>
                <x-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.*')">{{ __('Новости') }}</x-nav-link>
            @endauth
        </nav>

        <div class="ml-auto flex items-center gap-2">
            <x-locale-switch/>
            <x-theme-toggle/>

            @auth
                <form method="POST" action="{{ route('logout') }}" class="contents">
                    @csrf
                    <button type="submit" class="btn-ghost" title="{{ __('Выйти') }}">
                        <span class="hidden sm:inline">{{ auth()->user()->fullName() }}</span>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                        </svg>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn-primary">{{ __('Войти') }}</a>
            @endauth
        </div>
    </div>
</header>

@if (session('status'))
    <div class="mx-auto mt-4 max-w-6xl px-4">
        <div class="surface flex items-center gap-3 px-4 py-3 text-sm">
            <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
            {{ session('status') }}
        </div>
    </div>
@endif

<main class="mx-auto max-w-6xl px-4 py-8">
    @yield('content')
</main>

<footer class="mt-16 border-t py-8 text-sm muted" style="border-color: var(--line)">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-2 px-4">
        <span>ItCube</span>
        <span>{{ __('Код исполняется на Wandbox, а не на этом сервере') }}</span>
        <a class="ml-auto hover:underline" href="https://github.com/dripips/ItCube">GitHub</a>
    </div>
</footer>
</body>
</html>
