@extends('layouts.nav')

@section('title', 'School news')

@section('content')
    @php
        $user = auth()->user();
    @endphp

    <div class="page-heading news-heading">
        <div>
            <p>School news</p>
            <h1>Announcements.</h1>
            <span>Teachers and admins can post news. Students can read everything here.</span>
        </div>

        @if ($user->canPostSchoolContent())
            <a class="news-new-button" href="#new-news-post">
                <span class="material-symbols-outlined">add</span>
                New post
            </a>
        @endif
    </div>

    @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="news-list">
        @forelse ($posts as $post)
            <article class="news-card">
                @php
                    // created_at is the time Laravel saved when this news post was first created.
                    $postedAt = $post->created_at->timezone(config('app.timezone'))->format('d M Y, H:i');
                @endphp

                @if ($user->isAdmin() || $post->author_id === $user->id)
                    <details class="news-post-menu">
                        <summary>
                            <span class="material-symbols-outlined">more_horiz</span>
                        </summary>

                        <div>
                            <a href="#edit-news-post-{{ $post->id }}">Edit</a>

                            <form method="POST" action="{{ route('news.destroy', $post) }}" onsubmit="return confirm('Delete this news post?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit">Delete</button>
                            </form>
                        </div>
                    </details>
                @endif

                @if ($post->image_path)
                    <img src="{{ asset($post->image_path) }}" alt="{{ $post->title }}">
                @endif

                <div>
                    <p>
                        {{ $post->author->name }} · Posted {{ $postedAt }}

                        @if ($post->updated_at->gt($post->created_at->copy()->addSecond()))
                            · edited
                        @endif
                    </p>
                    <h2>{{ $post->title }}</h2>

                    @if ($post->body)
                        <span>{{ $post->body }}</span>
                    @endif
                </div>
            </article>
        @empty
            <div class="empty-card">
                <p>No news has been posted yet.</p>
            </div>
        @endforelse
    </section>

    @if ($user->canPostSchoolContent())
        <div id="new-news-post" class="news-overlay">
            <form class="news-overlay-form" method="POST" action="{{ route('news.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="news-overlay-top">
                    <div>
                        <p>New post</p>
                        <h2>Post news</h2>
                    </div>
                    <a href="#">Close</a>
                </div>

                <input name="title" type="text" value="{{ old('title') }}" placeholder="News title" required>
                <textarea name="body" rows="5" placeholder="Write the announcement...">{{ old('body') }}</textarea>

                <label for="news-image">
                    <span class="material-symbols-outlined">image</span>
                    Add photo
                </label>
                <input id="news-image" name="image" type="file" accept="image/*">

                <button type="submit">Post</button>
            </form>
        </div>
    @endif

    @foreach ($posts as $post)
        @if ($user->isAdmin() || $post->author_id === $user->id)
            <div id="edit-news-post-{{ $post->id }}" class="news-overlay">
                <form class="news-overlay-form" method="POST" action="{{ route('news.update', $post) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="news-overlay-top">
                        <div>
                            <p>Edit post</p>
                            <h2>{{ $post->title }}</h2>
                        </div>
                        <a href="#">Close</a>
                    </div>

                    <input name="title" type="text" value="{{ old('title', $post->title) }}" placeholder="News title" required>
                    <textarea name="body" rows="5" placeholder="Write the announcement...">{{ old('body', $post->body) }}</textarea>

                    <label for="news-image-{{ $post->id }}">
                        <span class="material-symbols-outlined">image</span>
                        Replace photo
                    </label>
                    <input id="news-image-{{ $post->id }}" name="image" type="file" accept="image/*">

                    <button type="submit">Save</button>
                </form>
            </div>
        @endif
    @endforeach
@endsection
