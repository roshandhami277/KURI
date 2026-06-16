<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisterController extends Controller
{
    // Show the registration form.
    public function create(): View
    {
        return view('auth.register', [
            'courses' => Course::where('is_active', true)->orderBy('name')->get(),
            'schoolClasses' => SchoolClass::where('is_active', true)
                ->orderBy('grade_level')
                ->orderBy('name')
                ->get(),
        ]);
    }

    // Validate the form, create the account, and log the new user in.
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
                'regex:/^([A-Za-z0-9._%+-]+@ael\.edu\.pt|a[0-9]{5}@alunos\.ael\.edu\.pt)$/i',
                // ^     start of email
                // $     end of email
                // |     OR
                // +     one or more
                // {5}   exactly 5
                // \.    real dot
                // /i    ignore uppercase/lowercase
            ],
            'password' => ['required', 'confirmed', 'min:5'],
            'course_id' => [
                'required',
                Rule::exists('courses', 'id')->where('is_active', true),
            ],
            'school_class_id' => [
                'required',
                Rule::exists('school_classes', 'id')->where('is_active', true),
            ],
        ], [
            'email.regex' => 'Please use your correct school email.',
        ]);

        // The User model automatically hashes the password before saving it.
        $user = User::create($validated);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
