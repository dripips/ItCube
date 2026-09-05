@extends('layouts.app')
@section('title', __('Вход').' — ItCube')

@section('content')
    <div class="mx-auto max-w-sm py-10">
        <div class="mb-8 text-center">
            <x-cube class="mx-auto h-12 w-12"/>
            <h1 class="mt-4 text-2xl font-bold">{{ __('Вход в ItCube') }}</h1>
            <p class="mt-1 text-sm muted">{{ __('Логин и пароль выдаёт преподаватель') }}</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="surface space-y-4 p-6">
            @csrf

            <div>
                <label for="username" class="mb-1.5 block text-sm font-medium">{{ __('Логин') }}</label>
                <input id="username" name="username" value="{{ old('username') }}" required autofocus
                       autocomplete="username" class="field" @error('username') aria-invalid="true" @enderror>
                @error('username')
                    <p class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium">{{ __('Пароль') }}</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" class="field">
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1" class="rounded">
                {{ __('Запомнить меня') }}
            </label>

            <button type="submit" class="btn-primary w-full">{{ __('Войти') }}</button>
        </form>
    </div>
@endsection
