<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::post('login', [Api\AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('api.login');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('me', [Api\AuthController::class, 'me'])->name('api.me');
    Route::post('logout', [Api\AuthController::class, 'logout'])->name('api.logout');

    Route::get('schedule', Api\ScheduleController::class)->name('api.schedule');

    Route::middleware('role:student')->prefix('learn')->group(function (): void {
        Route::get('lessons', [Api\LearnController::class, 'lessons'])->name('api.lessons.index');
        Route::get('lessons/{lesson}', [Api\LearnController::class, 'lesson'])->name('api.lessons.show');
        Route::get('assignments/{assignment}', [Api\LearnController::class, 'assignment'])->name('api.assignments.show');
        Route::post('assignments/{assignment}/run', [Api\LearnController::class, 'run'])
            ->middleware('throttle:20,1')->name('api.assignments.run');
        Route::post('assignments/{assignment}/submit', [Api\LearnController::class, 'submit'])
            ->middleware('throttle:20,1')->name('api.assignments.submit');
        Route::get('submissions/{submission}', [Api\LearnController::class, 'submission'])->name('api.submissions.show');
    });

    Route::middleware('role:guardian')->prefix('family')->group(function (): void {
        Route::get('children', [Api\FamilyController::class, 'children'])->name('api.children.index');
        Route::get('children/{child}', [Api\FamilyController::class, 'child'])->name('api.children.show');
    });

    Route::middleware('role:teacher')->prefix('teach')->group(function (): void {
        Route::get('groups', [Api\TeachController::class, 'groups'])->name('api.groups.index');
        Route::get('journal', [Api\TeachController::class, 'journal'])->name('api.journal.show');
        Route::post('journal', [Api\TeachController::class, 'saveJournal'])->name('api.journal.store');
    });
});
