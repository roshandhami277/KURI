<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DailyTaskController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// Anyone can visit the homepage.
Route::get('/', function () {
    return view('home');
})->name('home');

// The guest middleware stops logged-in users from opening login/register again.
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

// The auth middleware protects these routes from visitors who are not logged in.
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    /*
     * These pages are placeholders for now.
     * Each route opens the same simple view with a different title.
     */
    //TASK ROUTES
    Route::get('/tasks', [DailyTaskController::class, 'index'])->name('tasks');
    Route::post('/tasks', [DailyTaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}', [DailyTaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/toggle', [DailyTaskController::class, 'toggle'])->name('tasks.toggle');
    Route::delete('/tasks/{task}', [DailyTaskController::class, 'destroy'])->name('tasks.destroy');

    //CALENDAR ROUTES
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::post('/calendar', [CalendarController::class, 'store'])->name('calendar.store');
    Route::patch('/calendar/{event}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/{event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

    //GRADE ROUTES
    Route::get('/grades', [GradeController::class, 'index'])->name('grades');
    Route::post('/grades', [GradeController::class, 'store'])->name('grades.store');
    Route::patch('/grades/{grade}', [GradeController::class, 'update'])->name('grades.update');
    Route::delete('/grades/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy');

    //NOTE ROUTES
    Route::get('/notes', [NoteController::class, 'index'])->name('notes');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::patch('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    //CHAT ROUTES
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::post('/chat', [ChatController::class, 'store'])->name('chat.store');
    Route::patch('/chat/{message}', [ChatController::class, 'update'])->name('chat.update');
    Route::delete('/chat/{message}', [ChatController::class, 'destroy'])->name('chat.destroy');

    Route::view('/news', 'workspace.placeholder', ['title' => 'School news'])->name('news');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
