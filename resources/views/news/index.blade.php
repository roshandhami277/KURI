@extends('layouts.nav')

@section('title', 'Notícias da escola')

@section('content')
    @php
        $user = auth()->user();
    @endphp

    <div class="page-heading news-heading">
        <div>
            <p>Notícias da escola</p>
            <h1>Avisos.</h1>
            <span>Professores e administradores podem publicar notícias. Os alunos podem ler tudo aqui.</span>
        </div>

        @if ($user->canPostSchoolContent())
            <a class="news-new-button" href="#new-news-post">
                <span class="material-symbols-outlined">add</span>
                Nova publicação
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
                            <a href="#edit-news-post-{{ $post->id }}">Editar</a>

                            <form method="POST" action="{{ route('news.destroy', $post) }}" onsubmit="return confirm(@json('Eliminar esta notícia?'));">
                                @csrf
                                @method('DELETE')

                                <button type="submit">Eliminar</button>
                            </form>
                        </div>
                    </details>
                @endif

                @if ($post->image_path)
                    <img src="{{ asset($post->image_path) }}" alt="{{ $post->title }}">
                @endif

                <div>
                    <p>
                        {{ $post->author->name }} · Publicado {{ $postedAt }}

                        @if ($post->updated_at->gt($post->created_at->copy()->addSecond()))
                            · editado
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
                <p>Ainda não foi publicada nenhuma notícia.</p>
            </div>
        @endforelse
    </section>

    @if ($user->canPostSchoolContent())
        <div id="new-news-post" class="news-overlay">
            <form class="news-overlay-form" method="POST" action="{{ route('news.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="news-overlay-top">
                    <div>
                        <p>Nova publicação</p>
                        <h2>Publicar notícia</h2>
                    </div>
                    <a href="#">Fechar</a>
                </div>

                <input name="title" type="text" value="{{ old('title') }}" placeholder="Título da notícia" required>
                <textarea name="body" rows="5" placeholder="Escreve o aviso...">{{ old('body') }}</textarea>

                <label for="news-image">
                    <span class="material-symbols-outlined">image</span>
                    Adicionar foto
                </label>
                <input id="news-image" name="image" type="file" accept="image/*">

                <button type="submit">Publicar</button>
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
                            <p>Editar publicação</p>
                            <h2>{{ $post->title }}</h2>
                        </div>
                        <a href="#">Fechar</a>
                    </div>

                    <input name="title" type="text" value="{{ old('title', $post->title) }}" placeholder="Título da notícia" required>
                    <textarea name="body" rows="5" placeholder="Escreve o aviso...">{{ old('body', $post->body) }}</textarea>

                    <label for="news-image-{{ $post->id }}">
                        <span class="material-symbols-outlined">image</span>
                        Substituir foto
                    </label>
                    <input id="news-image-{{ $post->id }}" name="image" type="file" accept="image/*">

                    <button type="submit">Guardar</button>
                </form>
            </div>
        @endif
    @endforeach
@endsection
