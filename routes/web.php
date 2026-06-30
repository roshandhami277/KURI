<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DailyTaskController;
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

    Route::view('/grades', 'workspace.placeholder', ['title' => 'Grades'])->name('grades');
    Route::view('/notes', 'workspace.placeholder', ['title' => 'Notes'])->name('notes');
    Route::view('/chat', 'workspace.placeholder', ['title' => 'Chat'])->name('chat');
    Route::view('/news', 'workspace.placeholder', ['title' => 'School news'])->name('news');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
