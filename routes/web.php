<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
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
     * Later, we can replace one placeholder at a time with a real feature.
     */
    Route::get('/tasks', [DailyTaskController::class, 'index'])->name('tasks');
    Route::post('/tasks', [DailyTaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [DailyTaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/toggle', [DailyTaskController::class, 'toggle'])->name('tasks.toggle');
    Route::delete('/tasks/{task}', [DailyTaskController::class, 'destroy'])->name('tasks.destroy');

    Route::view('/calendar', 'workspace.placeholder', ['title' => 'Calendar'])->name('calendar');
    Route::view('/grades', 'workspace.placeholder', ['title' => 'Grades'])->name('grades');
    Route::view('/notes', 'workspace.placeholder', ['title' => 'Notes'])->name('notes');
    Route::view('/chat', 'workspace.placeholder', ['title' => 'Chat'])->name('chat');
    Route::view('/news', 'workspace.placeholder', ['title' => 'School news'])->name('news');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
