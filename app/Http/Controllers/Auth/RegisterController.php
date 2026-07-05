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
        $email = strtolower((string) $request->input('email'));
        $role = str_contains($email, '@alunos.') ? 'student' : 'teacher';

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
            'password' => ['required', 'confirmed', 'min:8'],
            'course_id' => [
                'required',
                Rule::exists('courses', 'id')->where('is_active', true),
            ],
            'school_class_id' => [
                'required',
                Rule::exists('school_classes', 'id')->where('is_active', true),
            ],
        ], [
            'name.required' => 'Escreve o teu nome.',
            'email.required' => 'Escreve o teu email escolar.',
            'email.email' => 'Escreve um email válido.',
            'email.unique' => 'Já existe uma conta com este email.',
            'email.regex' => 'Usa o teu email escolar correto.',
            'password.required' => 'Escreve uma palavra-passe.',
            'password.confirmed' => 'As palavras-passe não correspondem.',
            'password.min' => 'A palavra-passe tem de ter pelo menos 8 caracteres.',
            'course_id.required' => 'Escolhe o curso a que pertences.',
            'course_id.exists' => 'Escolhe um curso válido.',
            'school_class_id.required' => 'Escolhe a tua turma ou grupo de DT.',
            'school_class_id.exists' => 'Escolhe uma turma válida.',
        ]);

        // Students are detected by @alunos.ael.edu.pt.
        // Teachers use a normal @ael.edu.pt email.
        // Admins are not created by the public register form; you can promote them later.
        $validated['role'] = $role;

        // The User model automatically hashes the password before saving it.
        $user = User::create($validated);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
