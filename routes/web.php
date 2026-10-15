<?php

use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LearningActivityController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/lessons', [LessonController::class, 'index'])->name('lessons.index');
    Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
    Route::put('/lessons/{lesson}/progress', [LearningActivityController::class, 'progress'])->name('lessons.progress');
    Route::put('/lessons/{lesson}/bookmark', [LearningActivityController::class, 'bookmark'])->name('lessons.bookmark');
    Route::post('/lessons/{lesson}/quiz', [QuizController::class, 'store'])->middleware('throttle:30,1')->name('lessons.quiz');
    Route::put('/learning-goal', [LearningActivityController::class, 'goal'])->name('goals.update');
    Route::get('/history', [LearningActivityController::class, 'history'])->name('history');
    Route::prefix('admin')->name('admin.')->middleware('can:manage-content')->group(function () {
        Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
        Route::post('/tags', [TagController::class, 'save'])->name('tags.store');
        Route::put('/tags/{tag}', [TagController::class, 'save'])->name('tags.update');
        Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
        Route::get('/', [ContentController::class, 'index'])->name('index');
        Route::get('/lessons/create', [ContentController::class, 'create'])->name('lessons.create');
        Route::post('/lessons', [ContentController::class, 'store'])->name('lessons.store');
        Route::get('/lessons/{lesson}/edit', [ContentController::class, 'edit'])->name('lessons.edit');
        Route::put('/lessons/{lesson}', [ContentController::class, 'update'])->name('lessons.update');
        Route::post('/modules', [ContentController::class, 'module'])->name('modules.store');
        Route::put('/modules/{module}', [ContentController::class, 'module'])->name('modules.update');
        Route::get('/modules/{module}/order', [ContentController::class, 'order'])->name('modules.order');
        Route::put('/modules/{module}/order', [ContentController::class, 'reorder'])->name('modules.reorder');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
