<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ресурс оборачивается в data, только когда его возвращают из метода
        // напрямую. Внутри массива он остаётся плоским, и форма ответа зависела
        // от того, как написан контроллер. Обёртка выключена, а конверт каждый
        // адрес ставит сам.
        JsonResource::withoutWrapping();

        //
    }
}
