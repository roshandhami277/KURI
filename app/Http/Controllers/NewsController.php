<?php

namespace App\Http\Controllers;

use App\Models\NewsPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        return view('news.index', [
            'posts' => NewsPost::with('author')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canPostSchoolContent(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['nullable', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $postData = [
            'author_id' => $request->user()->id,
            'title' => $validated['title'],
            'body' => $validated['body'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $postData['image_path'] = $this->storeImage($request);
        }

        NewsPost::create($postData);

        return redirect()->route('news');
    }

    public function update(Request $request, NewsPost $post): RedirectResponse
    {
        // Admins can edit any post. Teachers can edit only posts they wrote.
        abort_unless($request->user()->isAdmin() || $post->author_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['nullable', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $postData = [
            'title' => $validated['title'],
            'body' => $validated['body'] ?? null,
        ];

        if ($request->hasFile('image')) {
            if ($post->image_path) {
                File::delete(public_path($post->image_path));
            }

            $postData['image_path'] = $this->storeImage($request);
        }

        $post->update($postData);

        return redirect()->route('news');
    }

    public function destroy(Request $request, NewsPost $post): RedirectResponse
    {
        // Admins can delete any post. Teachers can delete only posts they wrote.
        abort_unless($request->user()->isAdmin() || $post->author_id === $request->user()->id, 403);

        if ($post->image_path) {
            File::delete(public_path($post->image_path));
        }

        $post->delete();

        return redirect()->route('news');
    }

    private function storeImage(Request $request): string
    {
        $file = $request->file('image');
        $folder = public_path('news_uploads');

        if (! File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        $fileName = uniqid('news_', true).'.'.$file->getClientOriginalExtension();
        $file->move($folder, $fileName);

        return 'news_uploads/'.$fileName;
    }
}
