@extends('layouts.nav')

@section('title', 'Apontamentos')

@section('content')
    @php
        $tagLabels = [
            'study' => 'Estudo',
            'personal' => 'Pessoal',
            'important' => 'Importante',
        ];
    @endphp

    <div class="notes-cover" aria-hidden="true"></div>

    <div class="page-heading">
        <p>Apontamentos de estudo</p>
        <h1>Apontamentos</h1>
        <span>Escreve documentos privados, organiza-os com etiquetas simples e abre cada apontamento como um pequeno espaço de trabalho.</span>
        <small class="note-open-hint">Clica numa linha de apontamento para abrir. Altera o que quiseres e depois carrega em Guardar no topo.</small>
    </div>

    @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if (session('success'))
        <p class="success-message">{{ session('success') }}</p>
    @endif

    <section class="notes-page">
        <div class="notes-workspace">
            <div class="notes-database-top">
                <div class="note-view-tabs">
                    <button class="active" type="button" data-note-tab="all">
                        <span class="material-symbols-outlined">format_list_bulleted</span>
                        Todos os apontamentos
                    </button>
                    <button type="button" data-note-tab="study">
                        <span class="material-symbols-outlined">format_list_bulleted</span>
                        Apontamentos de estudo
                    </button>
                    <button type="button" data-note-tab="personal">
                        <span class="material-symbols-outlined">format_list_bulleted</span>
                        Apontamentos pessoais
                    </button>
                    <button type="button" data-note-tab="important">
                        <span class="material-symbols-outlined">format_list_bulleted</span>
                        Importante
                    </button>
                </div>

                <div class="note-top-actions">
                    <a class="note-new-button" href="#new-note" data-open-new-note>
                        Novo
                        <span>+</span>
                    </a>
                </div>
            </div>

            <div class="notes-list">
                <a class="note-line note-new-line" href="#new-note" data-open-new-note>
                    <span>
                        <span class="material-symbols-outlined">note_add</span>
                        <strong>Novo apontamento</strong>
                    </span>
                    <small></small>
                    <small></small>
                    <small></small>
                </a>

                @forelse ($notes as $note)
                    <div
                        class="note-line"
                        data-note-row
                        data-open-note="#note-{{ $note->id }}"
                        data-note-tag="{{ strtolower($note->tag ?? '') }}"
                        data-note-date="{{ $note->created_at->format('Y-m-d') }}"
                    >
                        <a class="note-line-title" href="#note-{{ $note->id }}">
                            <span class="material-symbols-outlined">description</span>
                            <strong>{{ $note->title }}</strong>
                        </a>

                        <div class="note-line-tags">
                            @if ($note->tag)
                                <span class="note-tag note-tag-{{ $note->tag }}">{{ $tagLabels[$note->tag] ?? $note->tag }}</span>
                            @endif

                            @if ($note->tag === 'study' && $note->subject)
                                <span class="note-subject-tag">{{ $note->subject->name }}</span>
                            @endif
                        </div>

                        <time>{{ $note->created_at->format('d/m/Y H:i') }}</time>

                        <details class="note-menu">
                            <summary aria-label="Abrir menu do apontamento">
                                <span class="material-symbols-outlined">more_horiz</span>
                            </summary>

                            <div>
                                <form method="POST" action="{{ route('notes.destroy', $note) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Eliminar</button>
                                </form>

                                <a href="#share-note-{{ $note->id }}">Partilhar no grupo</a>
                            </div>
                        </details>
                    </div>
                @empty
                    <p class="note-empty" data-note-empty>Ainda não há apontamentos. Clica em “Novo” para começar.</p>
                @endforelse

                @if ($notes->isNotEmpty())
                    <p class="note-empty" data-note-empty hidden>Nenhum apontamento corresponde a este filtro.</p>
                @endif
            </div>
        </div>
    </section>

    <div id="new-note" class="note-panel-layer">
        <form class="note-panel" method="POST" action="{{ route('notes.store') }}">
            @csrf
            <input type="hidden" name="opened_at" data-new-note-opened-at>

            <div class="note-document-toolbar">
                <a href="#" class="overlay-close">Fechar</a>
                <button type="submit">Guardar</button>
            </div>

            <input class="note-document-title" id="new-note-title" name="title" type="text" placeholder="Nova página" required>

            <div class="note-properties">
                <div class="note-property-row">
                    <span class="material-symbols-outlined">schedule</span>
                    <label>Criado</label>
                    <p data-new-note-created-text>{{ now()->format('d/m/Y H:i') }}</p>
                </div>

                <div class="note-property-row">
                    <span class="material-symbols-outlined">sell</span>
                    <label for="new-note-tag">Etiqueta</label>
                    <select id="new-note-tag" name="tag" data-note-tag-select>
                        <option value="">Sem etiqueta</option>
                        <option value="study">Estudar</option>
                        <option value="personal">Pessoal</option>
                        <option value="important">Importante</option>
                    </select>
                </div>

                <div class="note-property-row" data-subject-field hidden>
                    <span class="material-symbols-outlined">school</span>
                    <label for="new-note-subject">Disciplina opcional</label>
                    <select id="new-note-subject" name="subject_id">
                        <option value="">Sem disciplina</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="note-property-row">
                    <span class="material-symbols-outlined">notes</span>
                    <label for="new-note-description">Descrição</label>
                    <input id="new-note-description" name="description" type="text" placeholder="Pequeno resumo">
                </div>
            </div>

            <textarea class="note-document-body" id="new-note-body" name="body" placeholder="Carrega em Enter e começa a escrever..."></textarea>

        </form>
    </div>

    @foreach ($notes as $note)
        <div id="note-{{ $note->id }}" class="note-panel-layer">
            <form class="note-panel" method="POST" action="{{ route('notes.update', $note) }}">
                @csrf
                @method('PATCH')

                <div class="note-document-toolbar">
                    <a href="#" class="overlay-close">Fechar</a>
                    <button type="submit">Guardar</button>
                </div>

                <input class="note-document-title" id="note-title-{{ $note->id }}" name="title" type="text" value="{{ $note->title }}" required>

                <div class="note-properties">
                    <div class="note-property-row">
                        <span class="material-symbols-outlined">schedule</span>
                        <label>Criado</label>
                    <p>{{ $note->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <div class="note-property-row">
                        <span class="material-symbols-outlined">sell</span>
                        <label for="note-tag-{{ $note->id }}">Etiqueta</label>
                        <select id="note-tag-{{ $note->id }}" name="tag" data-note-tag-select>
                            <option value="">Sem etiqueta</option>
                            <option value="study" @selected($note->tag === 'study')>Estudar</option>
                            <option value="personal" @selected($note->tag === 'personal')>Pessoal</option>
                            <option value="important" @selected($note->tag === 'important')>Importante</option>
                        </select>
                    </div>

                    <div class="note-property-row" data-subject-field @if ($note->tag !== 'study') hidden @endif>
                        <span class="material-symbols-outlined">school</span>
                        <label for="note-subject-{{ $note->id }}">Disciplina opcional</label>
                        <select id="note-subject-{{ $note->id }}" name="subject_id">
                            <option value="">Sem disciplina</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected($note->subject_id === $subject->id)>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="note-property-row">
                        <span class="material-symbols-outlined">notes</span>
                        <label for="note-description-{{ $note->id }}">Descrição</label>
                        <input id="note-description-{{ $note->id }}" name="description" type="text" value="{{ $note->description }}" placeholder="Pequeno resumo">
                    </div>
                </div>

                <textarea class="note-document-body" id="note-body-{{ $note->id }}" name="body" placeholder="Carrega em Enter e começa a escrever...">{{ $note->body }}</textarea>

            </form>
        </div>
    @endforeach

    @foreach ($notes as $note)
        <div id="share-note-{{ $note->id }}" class="note-share-layer">
            <div class="note-share-box">
                <div class="note-share-top">
                    <div>
                        <p>Partilhar apontamento</p>
                        <h2>{{ $note->title }}</h2>
                    </div>
                    <a href="#">Fechar</a>
                </div>

                <div class="note-share-list">
                    @forelse ($shareTargets as $target)
                        <form class="note-share-row" method="POST" action="{{ route('notes.share', $note) }}">
                            @csrf
                            <input type="hidden" name="target_type" value="{{ $target['type'] }}">
                            <input type="hidden" name="target_id" value="{{ $target['id'] }}">

                            <div>
                                <strong>{{ $target['name'] }}</strong>
                                <small>{{ $target['subtitle'] }}</small>
                            </div>

                            <button type="submit">Enviar</button>
                        </form>
                    @empty
                        <p class="note-share-empty">Ainda não estás em nenhum grupo.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach

    <script>
        var tabButtons = document.querySelectorAll('[data-note-tab]');
        var noteRows = document.querySelectorAll('[data-note-row]');
        var emptyMessage = document.querySelector('[data-note-empty]');
        var newNoteOpenedAt = document.querySelector('[data-new-note-opened-at]');
        var newNoteCreatedText = document.querySelector('[data-new-note-created-text]');
        var selectedTab = 'all';

        function formatNoteDate(date) {
            return date.toLocaleString('pt-PT', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        document.querySelectorAll('[data-open-new-note]').forEach(function (link) {
            link.addEventListener('click', function () {
                var clickedAt = new Date();

                if (newNoteOpenedAt) {
                    newNoteOpenedAt.value = clickedAt.toISOString();
                }

                if (newNoteCreatedText) {
                    newNoteCreatedText.textContent = formatNoteDate(clickedAt);
                }
            });
        });

        document.querySelectorAll('[data-open-note]').forEach(function (row) {
            row.addEventListener('click', function (event) {
                if (event.target.closest('a, button, form, details, summary')) {
                    return;
                }

                window.location.hash = row.dataset.openNote;
            });
        });

        function filterNotes() {
            var visibleRows = 0;

            noteRows.forEach(function (row) {
                var tabMatches = selectedTab === 'all' || row.dataset.noteTag === selectedTab;
                var shouldShow = tabMatches;

                row.hidden = ! shouldShow;

                if (shouldShow) {
                    visibleRows++;
                }
            });

            if (emptyMessage && noteRows.length > 0) {
                emptyMessage.hidden = visibleRows > 0;
            }
        }

        tabButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                selectedTab = button.dataset.noteTab;

                tabButtons.forEach(function (tabButton) {
                    tabButton.classList.toggle('active', tabButton === button);
                });

                filterNotes();
            });
        });

        document.querySelectorAll('[data-note-tag-select]').forEach(function (select) {
            function updateTagFields() {
                var subjectField = select.closest('.note-panel').querySelector('[data-subject-field]');
                var subjectSelect = subjectField ? subjectField.querySelector('select') : null;

                if (! subjectField) {
                    return;
                }

                subjectField.hidden = select.value !== 'study';

                if (select.value !== 'study' && subjectSelect) {
                    subjectSelect.value = '';
                }

                select.classList.remove('tag-select-study', 'tag-select-personal', 'tag-select-important');

                if (select.value) {
                    select.classList.add('tag-select-' + select.value);
                }
            }

            select.addEventListener('change', updateTagFields);
            updateTagFields();
        });
    </script>
@endsection
