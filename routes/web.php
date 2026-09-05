<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DirectionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Learn;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Teach;
use Illuminate\Support\Facades\Route;

Route::post('locale', LocaleController::class)->name('locale');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('directions', [DirectionController::class, 'index'])->name('directions.index');
Route::get('directions/{direction}', [DirectionController::class, 'show'])->name('directions.show');
Route::get('news', [PostController::class, 'index'])->name('posts.index');
Route::get('news/{post}', [PostController::class, 'show'])->name('posts.show');

Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:10,1');
});

Route::post('logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:student'])->prefix('learn')->name('learn.')->group(function (): void {
    Route::get('/', [Learn\DashboardController::class, 'index'])->name('index');
    Route::get('schedule', [Learn\DashboardController::class, 'schedule'])->name('schedule');
    Route::get('lessons/{lesson}', [Learn\LessonController::class, 'show'])->name('lessons.show');

    Route::get('assignments/{assignment}', [Learn\AssignmentController::class, 'show'])->name('assignments.show');
    // Запуск без сдачи: один прогон на своём входе, ничего не записывается.
    Route::post('assignments/{assignment}/run', [Learn\AssignmentController::class, 'run'])
        ->middleware('throttle:20,1')->name('assignments.run');
    Route::post('assignments/{assignment}/submit', [Learn\AssignmentController::class, 'submit'])
        ->middleware('throttle:20,1')->name('assignments.submit');
    Route::get('submissions/{submission}/status', [Learn\AssignmentController::class, 'status'])->name('submissions.status');

    Route::get('quizzes/{quiz}', [Learn\QuizController::class, 'show'])->name('quizzes.show');
    Route::post('quizzes/{quiz}/start', [Learn\QuizController::class, 'start'])->name('quizzes.start');
    Route::post('attempts/{attempt}/finish', [Learn\QuizController::class, 'finish'])->name('quizzes.finish');

    Route::get('assessments', [Learn\AssessmentController::class, 'index'])->name('assessments');
    Route::get('assessments/{assessment}', [Learn\AssessmentController::class, 'show'])->name('assessments.show');
});

Route::middleware(['auth', 'role:teacher'])->prefix('teach')->name('teach.')->group(function (): void {
    Route::get('groups', [Teach\GroupController::class, 'index'])->name('groups.index');
    Route::get('groups/{group}', [Teach\GroupController::class, 'show'])->name('groups.show');

    Route::get('journal', [Teach\JournalController::class, 'index'])->name('journal.index');
    Route::post('journal', [Teach\JournalController::class, 'store'])->name('journal.store');

    Route::get('assessments', [Teach\AssessmentController::class, 'index'])->name('assessments.index');
    Route::get('assessments/{assessment}', [Teach\AssessmentController::class, 'show'])->name('assessments.show');
});
