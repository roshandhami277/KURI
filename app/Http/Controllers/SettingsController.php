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
        ]);

        // Students can change their display name.
        // Email, course, and class are locked because they affect school identity.
        $request->user()->update($validated);

        return redirect()->route('settings')->with('success', 'Name updated.');
    }
}
