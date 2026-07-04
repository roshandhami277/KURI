<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ChatGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $users = User::with(['course', 'schoolClass'])
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $groups = Course::withCount('users')
            ->orderBy('name')
            ->get();

        $teacherGroups = ChatGroup::with('teacher')
            ->withCount('members')
            ->orderBy('name')
            ->get();

        $adminCount = $users->where('role', 'admin')->count();

        return view('admin.index', [
            'users' => $users,
            // A group is just a course for now. Everyone with that course_id belongs to it.
            'groups' => $groups,
            'teacherGroups' => $teacherGroups,
            'adminCount' => $adminCount,
            'stats' => [
                'users' => $users->count(),
                'students' => $users->where('role', 'student')->count(),
                'teachers' => $users->where('role', 'teacher')->count(),
                'admins' => $adminCount,
                'groups' => $groups->count() + $teacherGroups->count(),
            ],
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['student', 'teacher', 'admin'])],
        ]);

        // Stop the admin from accidentally removing their own admin access.
        if ($request->user()->id === $user->id && $validated['role'] !== 'admin') {
            return back()->withErrors([
                'role' => 'You cannot remove your own admin role.',
            ]);
        }

        $userData = [
            'role' => $validated['role'],
        ];

        if ($validated['role'] === 'admin') {
            // Admins control the whole app, so they should not belong to one course or class.
            $userData['course_id'] = null;
            $userData['school_class_id'] = null;
        }

        $user->update($userData);

        return redirect()->route('admin.index');
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        // Stop the admin from deleting the account they are currently using.
        if ($request->user()->id === $user->id) {
            return back()->withErrors([
                'user' => 'You cannot delete your own account.',
            ]);
        }

        $user->delete();

        return redirect()->route('admin.index');
    }
}
