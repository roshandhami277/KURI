<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('settings.index', [
            'user' => $request->user()->load(['course', 'schoolClass']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $data = [
            'name' => $validated['name'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        // Every user can change only their own name and password.
        // Email, role, course, and class stay locked because they decide account access.
        $request->user()->update($data);

        return redirect()->route('settings')->with('success', 'Settings updated.');
    }
}
