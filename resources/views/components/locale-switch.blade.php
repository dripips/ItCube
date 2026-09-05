@php($current = app()->getLocale())

<form method="POST" action="{{ route('locale') }}" class="contents">
    @csrf
    <select name="locale"
            class="field w-auto py-1.5 text-xs"
            aria-label="{{ __('Язык') }}"
            onchange="this.form.submit()">
        @foreach (App\Support\Locales::AVAILABLE as $code => $name)
            <option value="{{ $code }}" @selected($code === $current)>{{ $name }}</option>
        @endforeach
    </select>
</form>
