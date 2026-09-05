<?php

namespace App\Http\Middleware;

use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Язык страницы: сначала выбор человека, затем язык учётной записи, затем
 * заголовок браузера. Выбор гостя живёт в сессии, выбор вошедшего — в профиле,
 * иначе он терялся бы при каждом входе с другого устройства.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (! Locales::supports($locale)) {
            $locale = $request->user()?->locale;
        }

        if (! Locales::supports($locale)) {
            $locale = Locales::fromHeader($request->header('Accept-Language'));
        }

        App::setLocale($locale);

        return $next($request);
    }
}
